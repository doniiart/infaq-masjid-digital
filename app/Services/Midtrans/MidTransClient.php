<?php

namespace App\Services\Midtrans;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransClient
{
    public function getTransactionStatus(string $orderId): array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $baseUrl = rtrim((string) config('services.midtrans.base_url'), '/');

        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi pada environment.');
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(10)
            ->get("{$baseUrl}/v2/{$orderId}/status");

        if (! $response->successful()) {
            throw new RuntimeException(
                "Gagal memverifikasi transaksi ke Midtrans Status API (HTTP {$response->status()}): " . $response->body()
            );
        }

        return $response->json() ?? [];
    }
}