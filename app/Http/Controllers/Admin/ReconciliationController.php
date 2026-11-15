<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqTransaction;
use App\Models\ReconciliationItem;
use App\Models\ReconciliationRun;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReconciliationController extends Controller
{
    public function index(): View
    {
        $runs = ReconciliationRun::with('triggeredByUser')
            ->orderByDesc('created_at')
            ->paginate(12);

        $stats = [
            'matched'        => InfaqTransaction::where('reconciliation_status', InfaqTransaction::RECON_MATCHED)->count(),
            'needs_review'   => InfaqTransaction::where('reconciliation_status', InfaqTransaction::RECON_NEEDS_REVIEW)->count(),
            'mismatch'       => InfaqTransaction::whereIn('reconciliation_status', [
                InfaqTransaction::RECON_AMOUNT_MISMATCH,
                InfaqTransaction::RECON_MISSING_IN_PROVIDER_REPORT,
                InfaqTransaction::RECON_DUPLICATE,
            ])->count(),
            'outside_scope'  => InfaqTransaction::where('reconciliation_status', InfaqTransaction::RECON_OUTSIDE_PROVIDER_SCOPE)->count(),
            'last_sync'      => ReconciliationRun::where('status', 'COMPLETED')->latest()->first()?->created_at,
        ];

        return view('admin.reconciliations.index', compact('runs', 'stats'));
    }

    public function show(ReconciliationRun $reconciliation): View
    {
        $reconciliation->load(['items.transaction', 'items.resolver', 'triggeredByUser']);

        return view('admin.reconciliations.show', compact('reconciliation'));
    }

    public function importManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_type'  => ['required', 'in:midtrans_report,external_bank_statement'],
            'period_start' => ['required', 'date'],
            'period_end'   => ['required', 'date', 'after_or_equal:period_start'],
            'report_file'  => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $path = $request->file('report_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (count($rows) < 2) {
            return back()->withErrors(['report_file' => 'File CSV kosong atau tidak memiliki baris data.']);
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $h))), $rows[0]);
        $dataRows = array_slice($rows, 1);

        $periodStart = Carbon::parse($validated['period_start'], 'Asia/Jakarta')->startOfDay();
        $periodEnd = Carbon::parse($validated['period_end'], 'Asia/Jakarta')->endOfDay();

        $run = DB::transaction(function () use ($validated, $headers, $dataRows, $periodStart, $periodEnd) {
            $run = ReconciliationRun::create([
                'source_type'          => $validated['source_type'],
                'period_start'         => $periodStart,
                'period_end'           => $periodEnd,
                'triggered_by_type'    => 'manual',
                'triggered_by_user_id' => Auth::id(),
                'status'               => 'RUNNING',
                'notes'                => $validated['notes'] ?? null,
            ]);

            $matchedCount = 0;
            $discrepancyCount = 0;
            $seenExternalIds = [];
            $matchedSystemIds = [];

            foreach ($dataRows as $row) {
                if (count($row) !== count($headers)) {
                    continue;
                }
                $record = array_combine($headers, $row);
                $extTrxId = trim($record['transaction_id'] ?? $record['reference_id'] ?? '');
                $extOrderId = trim($record['order_id'] ?? '');
                $extAmount = (float) ($record['gross_amount'] ?? $record['amount'] ?? 0);
                $extAcquirer = trim($record['acquirer'] ?? '');

                if ($extTrxId === '' && $extAmount <= 0) {
                    continue;
                }

                // Cek duplikat di dalam laporan provider sendiri
                if ($extTrxId !== '' && in_array($extTrxId, $seenExternalIds, true)) {
                    $discrepancyCount++;
                    ReconciliationItem::create([
                        'reconciliation_run_id'   => $run->id,
                        'external_transaction_id' => $extTrxId,
                        'external_order_id'       => $extOrderId ?: null,
                        'external_amount'         => $extAmount,
                        'external_acquirer'       => $extAcquirer ?: null,
                        'match_status'            => InfaqTransaction::RECON_DUPLICATE,
                        'resolution_notes'        => 'ID transaksi muncul lebih dari satu kali pada file laporan.',
                    ]);
                    continue;
                }

                if ($extTrxId !== '') {
                    $seenExternalIds[] = $extTrxId;
                }

                $systemTrx = $extTrxId !== ''
                    ? InfaqTransaction::where('provider_transaction_id', $extTrxId)->first()
                    : null;

                if (! $systemTrx) {
                    $discrepancyCount++;
                    $status = $validated['source_type'] === 'external_bank_statement'
                        ? InfaqTransaction::RECON_OUTSIDE_PROVIDER_SCOPE
                        : InfaqTransaction::RECON_MISSING_IN_DATABASE;

                    ReconciliationItem::create([
                        'reconciliation_run_id'   => $run->id,
                        'external_transaction_id' => $extTrxId ?: null,
                        'external_order_id'       => $extOrderId ?: null,
                        'external_amount'         => $extAmount,
                        'external_acquirer'       => $extAcquirer ?: null,
                        'match_status'            => $status,
                        'resolution_notes'        => $status === InfaqTransaction::RECON_OUTSIDE_PROVIDER_SCOPE
                            ? 'Tercatat pada mutasi eksternal di luar cakupan webhook Midtrans.'
                            : 'Tercatat di laporan Midtrans namun belum masuk ke database aplikasi.',
                    ]);
                    continue;
                }

                $matchedSystemIds[] = $systemTrx->id;

                if (abs((float) $systemTrx->gross_amount - $extAmount) > 0.01) {
                    $discrepancyCount++;
                    $systemTrx->update(['reconciliation_status' => InfaqTransaction::RECON_AMOUNT_MISMATCH]);

                    ReconciliationItem::create([
                        'reconciliation_run_id'   => $run->id,
                        'infaq_transaction_id'    => $systemTrx->id,
                        'external_transaction_id' => $extTrxId,
                        'external_order_id'       => $extOrderId ?: $systemTrx->provider_order_id,
                        'external_amount'         => $extAmount,
                        'system_amount'           => $systemTrx->gross_amount,
                        'external_acquirer'       => $extAcquirer ?: $systemTrx->acquirer,
                        'match_status'            => InfaqTransaction::RECON_AMOUNT_MISMATCH,
                        'resolution_notes'        => 'Selisih nominal antara database dan laporan penyedia.',
                    ]);
                } else {
                    $matchedCount++;
                    $systemTrx->update(['reconciliation_status' => InfaqTransaction::RECON_MATCHED]);

                    ReconciliationItem::create([
                        'reconciliation_run_id'   => $run->id,
                        'infaq_transaction_id'    => $systemTrx->id,
                        'external_transaction_id' => $extTrxId,
                        'external_order_id'       => $extOrderId ?: $systemTrx->provider_order_id,
                        'external_amount'         => $extAmount,
                        'system_amount'           => $systemTrx->gross_amount,
                        'external_acquirer'       => $extAcquirer ?: $systemTrx->acquirer,
                        'match_status'            => InfaqTransaction::RECON_MATCHED,
                    ]);
                }
            }

            // Cek transaksi di DB pada periode tersebut yang tidak ada di laporan Midtrans
            $systemRecords = InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
                ->whereBetween('transaction_time', [$periodStart, $periodEnd])
                ->get();

            if ($validated['source_type'] === 'midtrans_report') {
                foreach ($systemRecords as $orphan) {
                    if (! in_array($orphan->id, $matchedSystemIds, true)) {
                        $discrepancyCount++;
                        $orphan->update(['reconciliation_status' => InfaqTransaction::RECON_MISSING_IN_PROVIDER_REPORT]);

                        ReconciliationItem::create([
                            'reconciliation_run_id'   => $run->id,
                            'infaq_transaction_id'    => $orphan->id,
                            'external_transaction_id' => $orphan->provider_transaction_id,
                            'external_order_id'       => $orphan->provider_order_id,
                            'system_amount'           => $orphan->gross_amount,
                            'match_status'            => InfaqTransaction::RECON_MISSING_IN_PROVIDER_REPORT,
                            'resolution_notes'        => 'Ada di database lokal namun tidak ditemukan pada laporan resmi Midtrans.',
                        ]);
                    }
                }
            }

            $run->update([
                'total_provider_records' => count($seenExternalIds),
                'total_system_records'   => $systemRecords->count(),
                'total_matched'          => $matchedCount,
                'total_discrepancies'    => $discrepancyCount,
                'status'                 => 'COMPLETED',
            ]);

            return $run;
        });

        AuditLogger::log('RECONCILIATION_MANUAL_RUN', $run, null, $run->toArray());

        return redirect()
            ->route('admin.reconciliations.show', $run)
            ->with('success', 'Rekonsiliasi manual selesai dijalankan.');
    }

    public function resolveItem(Request $request, ReconciliationItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        $item->update([
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_by'      => Auth::id(),
            'resolved_at'      => now(),
        ]);

        AuditLogger::log('RECONCILIATION_ITEM_RESOLVED', $item, null, [
            'match_status'     => $item->match_status,
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        return back()->with('success', 'Catatan penyelesaian rekonsiliasi berhasil disimpan.');
    }
}