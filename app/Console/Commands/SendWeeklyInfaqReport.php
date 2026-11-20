<?php

namespace App\Console\Commands;

use App\Models\InfaqTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SendWeeklyInfaqReport extends Command
{
    protected $signature = 'infaq:send-weekly-report {--date= : Tanggal referensi YYYY-MM-DD (opsional untuk uji coba)}';

    protected $description = 'Generate laporan PDF infaq mingguan (Senin-Minggu) dan kirim ke n8n untuk diteruskan ke Grup WA';

    public function handle(): int
    {
        if (! filter_var(env('N8N_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->warn('N8N_ENABLED=false, pengiriman dilewati.');
            return self::SUCCESS;
        }

        $webhookUrl = env('N8N_WEBHOOK_WEEKLY_URL') ?: env('N8N_WEBHOOK_INFAQ_URL');
        $webhookSecret = env('N8N_WEBHOOK_SECRET', '');

        if (empty($webhookUrl)) {
            $this->error('URL webhook n8n belum diatur di .env.');
            return self::FAILURE;
        }

        $reference = $this->option('date')
            ? Carbon::parse($this->option('date'), 'Asia/Jakarta')
            : Carbon::now('Asia/Jakarta');

        $weekStart = $reference->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $reference->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $transactions = InfaqTransaction::query()
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->whereBetween('transaction_time', [
                $weekStart->copy()->utc(),
                $weekEnd->copy()->utc(),
            ])
            ->orderBy('transaction_time')
            ->get();

        $weeklyTotal = (float) $transactions->sum('gross_amount');
        $overallTotal = (float) InfaqTransaction::query()
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->sum('gross_amount');

        $periodLabel = $weekStart->format('d M Y') . ' - ' . $weekEnd->format('d M Y');
        $generatedAt = Carbon::now('Asia/Jakarta')->format('d M Y H:i');

        $pdf = Pdf::loadView('pdf.weekly-infaq-report', [
            'transactions' => $transactions,
            'weeklyTotal'  => $weeklyTotal,
            'overallTotal' => $overallTotal,
            'periodLabel'  => $periodLabel,
            'generatedAt'  => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan-infaq-' . $weekStart->format('Ymd') . '-' . $weekEnd->format('Ymd') . '.pdf';
        $relativePath = 'reports/' . $filename;

        Storage::disk('public')->put($relativePath, $pdf->output());

        $pdfUrl = rtrim(config('app.url'), '/') . '/storage/' . $relativePath;

        $response = Http::timeout(20)
            ->acceptJson()
            ->withHeaders([
                'X-Webhook-Secret' => $webhookSecret,
            ])
            ->post($webhookUrl, [
                'event_type'    => 'report.weekly_infaq_pdf',
                'period_label'  => $periodLabel,
                'week_start'    => $weekStart->toDateString(),
                'week_end'      => $weekEnd->toDateString(),
                'trx_count'     => $transactions->count(),
                'weekly_total'  => $weeklyTotal,
                'overall_total' => $overallTotal,
                'filename'      => $filename,
                'pdf_url'       => $pdfUrl,
            ]);

        if (! $response->successful()) {
            $this->error('Gagal mengirim ke n8n: HTTP ' . $response->status() . ' - ' . $response->body());
            return self::FAILURE;
        }

        $this->info("Laporan PDF mingguan ({$filename}) berhasil digenerate dan dikirim ke n8n.");
        return self::SUCCESS;
    }
}