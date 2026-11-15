<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        $transactions = $query->orderByDesc('transaction_time')
            ->paginate(15)
            ->withQueryString();

        $acquirers = InfaqTransaction::whereNotNull('acquirer')
            ->distinct()
            ->pluck('acquirer');

        return view('admin.transactions.index', compact('transactions', 'acquirers'));
    }

    public function show(InfaqTransaction $transaction): View
    {
        $transaction->load(['allocations.category', 'allocations.allocator', 'reconciliationItems.run']);
        $categories = InfaqCategory::where('is_active', true)->orderBy('id')->get();

        return view('admin.transactions.show', compact('transaction', 'categories'));
    }

    public function updateReview(Request $request, InfaqTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'review_status' => ['required', 'in:NOT_REVIEWED,VERIFIED,FLAGGED'],
        ]);

        $oldValues = ['review_status' => $transaction->review_status];

        $transaction->update([
            'review_status' => $validated['review_status'],
        ]);

        AuditLogger::log(
            action: 'TRANSACTION_REVIEW_UPDATED',
            auditable: $transaction,
            oldValues: $oldValues,
            newValues: ['review_status' => $transaction->review_status]
        );

        return back()->with('success', 'Status pemeriksaan Takmir berhasil diperbarui.');
    }

    public function allocate(Request $request, InfaqTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'infaq_category_id' => ['required', 'exists:infaq_categories,id'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        $oldAllocations = $transaction->allocations()
            ->with('category')
            ->get()
            ->map(fn ($a) => ['category' => $a->category?->name, 'amount' => $a->allocated_amount])
            ->toArray();

        DB::transaction(function () use ($transaction, $validated) {
            $transaction->allocations()->delete();

            FundAllocation::create([
                'infaq_transaction_id' => $transaction->id,
                'infaq_category_id'    => $validated['infaq_category_id'],
                'allocated_amount'     => $transaction->gross_amount,
                'allocated_by'         => Auth::id(),
                'notes'                => $validated['notes'] ?? null,
            ]);
        });

        $newCategory = InfaqCategory::find($validated['infaq_category_id']);

        AuditLogger::log(
            action: 'TRANSACTION_ALLOCATED',
            auditable: $transaction,
            oldValues: ['allocations' => $oldAllocations],
            newValues: [
                'category' => $newCategory?->name,
                'amount'   => $transaction->gross_amount,
                'notes'    => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Alokasi dana transaksi berhasil diperbarui ke ' . $newCategory?->name . '.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $transactions = $this->buildFilteredQuery($request)
            ->orderByDesc('transaction_time')
            ->get();

        AuditLogger::log('TRANSACTION_EXPORTED_CSV', null, null, ['count' => $transactions->count()]);

        $filename = 'transaksi-infaq-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel langsung membaca kolom & karakter Indonesia dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Transaction ID',
                'Order ID',
                'Merchant ID',
                'Waktu Transaksi (WIB)',
                'Waktu Settlement (WIB)',
                'Nominal (IDR)',
                'Metode',
                'Acquirer',
                'Issuer',
                'Status Pembayaran',
                'Status Rekonsiliasi',
                'Pemeriksaan Takmir',
                'Kategori Alokasi',
            ]);

            foreach ($transactions as $trx) {
                fputcsv($handle, [
                    $trx->provider_transaction_id,
                    $trx->provider_order_id,
                    $trx->merchant_id,
                    $trx->transaction_time->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    $trx->settlement_time?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') ?? '-',
                    $trx->gross_amount,
                    $trx->payment_type,
                    $trx->acquirer ?? '-',
                    $trx->issuer ?? '-',
                    $trx->transaction_status,
                    $trx->reconciliation_status,
                    $trx->review_status,
                    $trx->allocations->pluck('category.name')->filter()->join(', ') ?: 'Belum Dialokasikan',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPrint(Request $request): View
    {
        $transactions = $this->buildFilteredQuery($request)
            ->orderByDesc('transaction_time')
            ->get();

        AuditLogger::log('TRANSACTION_EXPORTED_PRINT', null, null, ['count' => $transactions->count()]);

        return view('admin.transactions.print', compact('transactions'));
    }

    private function buildFilteredQuery(Request $request)
    {
        return InfaqTransaction::query()
            ->with('allocations.category')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->input('search'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('provider_transaction_id', 'ilike', "%{$term}%")
                        ->orWhere('provider_order_id', 'ilike', "%{$term}%")
                        ->orWhere('merchant_cross_reference_id', 'ilike', "%{$term}%");
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_time', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_time', '<=', $request->input('date_to')))
            ->when($request->filled('min_amount'), fn ($q) => $q->where('gross_amount', '>=', $request->input('min_amount')))
            ->when($request->filled('max_amount'), fn ($q) => $q->where('gross_amount', '<=', $request->input('max_amount')))
            ->when($request->filled('transaction_status'), fn ($q) => $q->where('transaction_status', $request->input('transaction_status')))
            ->when($request->filled('review_status'), fn ($q) => $q->where('review_status', $request->input('review_status')))
            ->when($request->filled('reconciliation_status'), fn ($q) => $q->where('reconciliation_status', $request->input('reconciliation_status')))
            ->when($request->filled('acquirer'), fn ($q) => $q->where('acquirer', $request->input('acquirer')));
    }
}