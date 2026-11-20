<?php

namespace App\Console\Commands;

use App\Models\AutomationOutbox;
use App\Models\InfaqTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class DispatchAutomationOutbox extends Command
{
    protected $signature = 'outbox:dispatch {--limit=20 : Maksimal antrean per eksekusi}';

    protected $description = 'Mengirim antrean event dari automation_outbox ke Webhook n8n Cloud';

    public function handle(): int
    {
        $webhookUrl = (string) env('N8N_WEBHOOK_INFAQ_URL', '');
        $webhookSecret = (string) env('N8N_WEBHOOK_SECRET', '');

        if ($webhookUrl === '') {
            $this->warn('N8N_WEBHOOK_INFAQ_URL belum dikonfigurasi di .env.');
            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));

        $records = AutomationOutbox::query()
            ->where(function ($query) {
                $query->where('status', AutomationOutbox::STATUS_PENDING)
                    ->orWhere(function ($retryQuery) {
                        $retryQuery->where('status', AutomationOutbox::STATUS_FAILED)
                            ->whereColumn('retry_count', '<', 'max_retries')
                            ->where(function ($timeQuery) {
                                $timeQuery->whereNull('next_retry_at')
                                    ->orWhere('next_retry_at', '<=', now());
                            });
                    });
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada antrean outbox yang perlu dikirim.');
            return self::SUCCESS;
        }

        $totalSettlement = (float) InfaqTransaction::query()
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->sum('gross_amount');

        foreach ($records as $record) {
            $record->update(['status' => AutomationOutbox::STATUS_PROCESSING]);

            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeaders([
                        'X-Webhook-Secret' => $webhookSecret,
                    ])
                    ->post($webhookUrl, [
                        'outbox_id' => $record->id,
                        'event_id' => $record->event_id,
                        'event_type' => $record->event_type,
                        'aggregate_id' => $record->aggregate_id,
                        'payload' => $record->payload,
                        'summary' => [
                            'total_settlement' => $totalSettlement,
                        ],
                    ]);

                if ($response->successful()) {
                    $record->update([
                        'status' => AutomationOutbox::STATUS_PROCESSED,
                        'processed_at' => now(),
                        'last_error' => null,
                    ]);
                    $this->info("Outbox #{$record->id} berhasil dikirim ke n8n.");
                } else {
                    throw new RuntimeException("HTTP {$response->status()}: " . mb_substr($response->body(), 0, 500));
                }
            } catch (Throwable $e) {
                $record->update([
                    'status' => AutomationOutbox::STATUS_FAILED,
                    'retry_count' => $record->retry_count + 1,
                    'next_retry_at' => now()->addMinutes(2),
                    'last_error' => mb_substr($e->getMessage(), 0, 1000),
                ]);
                $this->error("Outbox #{$record->id} gagal: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}