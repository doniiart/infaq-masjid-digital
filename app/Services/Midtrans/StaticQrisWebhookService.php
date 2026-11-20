<?php

namespace App\Services\Midtrans;

use App\Models\AutomationOutbox;
use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use App\Models\PaymentProviderEvent;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class StaticQrisWebhookService
{
    public function __construct(
        private readonly MidtransClient $midtransClient
    ) {}

    /**
     * @return array{http_status: int, status: string, message: string, transaction_id?: string}
     */
    public function handle(array $payload, array $headers = []): array
    {
        $orderId       = isset($payload['order_id']) ? (string) $payload['order_id'] : '';
        $statusCode    = isset($payload['status_code']) ? (string) $payload['status_code'] : '';
        $grossAmount   = isset($payload['gross_amount']) ? (string) $payload['gross_amount'] : '';
        $signatureKey  = isset($payload['signature_key']) ? (string) $payload['signature_key'] : '';
        $transactionId = isset($payload['transaction_id']) ? (string) $payload['transaction_id'] : '';

        // 1. Simpan payload mentah terlebih dahulu untuk jejak audit forensik
        $providerEvent = PaymentProviderEvent::create([
            'provider'                => 'midtrans',
            'event_uuid'              => (string) Str::uuid(),
            'provider_transaction_id' => $transactionId !== '' ? $transactionId : null,
            'provider_order_id'       => $orderId !== '' ? $orderId : null,
            'signature_valid'         => false,
            'headers_json'            => $this->sanitizeHeaders($headers),
            'raw_payload'             => $payload,
            'processing_status'       => 'RECEIVED',
            'received_at'             => now(),
        ]);

        // 2. Validasi kelengkapan field wajib Midtrans
        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '' || $transactionId === '') {
            $providerEvent->update([
                'processing_status' => 'REJECTED_INCOMPLETE_PAYLOAD',
                'error_message'     => 'Field wajib (order_id, status_code, gross_amount, signature_key, transaction_id) tidak lengkap.',
            ]);

            return [
                'http_status' => 422,
                'status'      => 'invalid_payload',
                'message'     => 'Payload notifikasi tidak lengkap.',
            ];
        }

        // 3. Validasi Signature SHA512 resmi Midtrans
        if (! $this->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            $providerEvent->update([
                'processing_status' => 'REJECTED_INVALID_SIGNATURE',
                'error_message'     => 'Signature SHA512 tidak cocok dengan Server Key.',
            ]);

            return [
                'http_status' => 401,
                'status'      => 'invalid_signature',
                'message'     => 'Signature notifikasi tidak valid.',
            ];
        }

        $providerEvent->update(['signature_valid' => true]);

        // 4. Validasi Merchant ID & Mata Uang IDR
        $expectedMerchantId = (string) config('services.midtrans.merchant_id');
        $incomingMerchantId = isset($payload['merchant_id']) ? (string) $payload['merchant_id'] : '';

        if ($expectedMerchantId !== '' && $incomingMerchantId !== '' && ! hash_equals($expectedMerchantId, $incomingMerchantId)) {
            $providerEvent->update([
                'processing_status' => 'REJECTED_MERCHANT_MISMATCH',
                'error_message'     => "Merchant ID tidak sesuai: diterima {$incomingMerchantId}.",
            ]);

            return [
                'http_status' => 403,
                'status'      => 'merchant_mismatch',
                'message'     => 'Merchant ID tidak sesuai dengan konfigurasi masjid.',
            ];
        }

        $currency = strtoupper((string) ($payload['currency'] ?? 'IDR'));
        if ($currency !== 'IDR') {
            $providerEvent->update([
                'processing_status' => 'REJECTED_UNSUPPORTED_CURRENCY',
                'error_message'     => "Mata uang tidak didukung: {$currency}.",
            ]);

            return [
                'http_status' => 422,
                'status'      => 'invalid_currency',
                'message'     => 'Hanya transaksi mata uang IDR yang diterima.',
            ];
        }

        // 5. Validasi kanal pembayaran QRIS
        $paymentType = strtolower((string) ($payload['payment_type'] ?? ''));
        if (! in_array($paymentType, ['qris', 'gopay'], true) && ! str_contains($paymentType, 'qris')) {
            $providerEvent->update([
                'processing_status' => 'REJECTED_UNSUPPORTED_PAYMENT_TYPE',
                'error_message'     => "Payment type bukan QRIS: {$paymentType}.",
            ]);

            return [
                'http_status' => 422,
                'status'      => 'unsupported_payment_type',
                'message'     => 'Endpoint ini hanya memproses pembayaran QRIS Statis.',
            ];
        }

        // 6. Cek Idempotensi awal sebelum panggilan API eksternal
        if (InfaqTransaction::where('provider_transaction_id', $transactionId)->exists()) {
            $providerEvent->update(['processing_status' => 'IGNORED_DUPLICATE']);

            return [
                'http_status'    => 200,
                'status'         => 'duplicate_ignored',
                'message'        => 'Transaksi sudah tercatat sebelumnya (idempotent).',
                'transaction_id' => $transactionId,
            ];
        }

        $rawStatus = strtolower((string) ($payload['transaction_status'] ?? ''));

        // Status intermediate 'pending' belum final; tunggu webhook settlement/expire berikutnya
        if ($rawStatus === 'pending') {
            $providerEvent->update(['processing_status' => 'IGNORED_PENDING']);

            return [
                'http_status'    => 200,
                'status'         => 'pending_acknowledged',
                'message'        => 'Notifikasi pending diterima, menunggu status final.',
                'transaction_id' => $transactionId,
            ];
        }

        $mappedStatus = $this->mapTransactionStatus($rawStatus, $payload['fraud_status'] ?? null);
        $verifiedPayload = $payload;

        // 7. Untuk status SETTLEMENT, wajib verifikasi silang ke Midtrans Get Status API
        if ($mappedStatus === InfaqTransaction::STATUS_SETTLEMENT) {
            try {
                $statusResponse = $this->midtransClient->getTransactionStatus($orderId);
                $apiStatus = strtolower((string) ($statusResponse['transaction_status'] ?? ''));
                $apiAmount = (string) ($statusResponse['gross_amount'] ?? '');
                $apiTrxId  = (string) ($statusResponse['transaction_id'] ?? '');

                if (
                    ! in_array($apiStatus, ['settlement', 'capture'], true)
                    || ($apiTrxId !== '' && $apiTrxId !== $transactionId)
                    || ($apiAmount !== '' && abs((float) $apiAmount - (float) $grossAmount) > 0.01)
                ) {
                    $mappedStatus = InfaqTransaction::STATUS_NEEDS_REVIEW;
                } else {
                    $verifiedPayload = array_merge($payload, $statusResponse);
                }
            } catch (Throwable $e) {
                $providerEvent->update([
                    'processing_status' => 'FAILED_STATUS_VERIFICATION',
                    'error_message'     => $e->getMessage(),
                ]);

                // Balas 503 agar Midtrans melakukan retry otomatis saat API gangguan sementara
                return [
                    'http_status' => 503,
                    'status'      => 'verification_unavailable',
                    'message'     => 'Gagal memverifikasi status transaksi ke penyedia pembayaran.',
                ];
            }
        }

        // 8. Simpan transaksi, alokasi kategori default, dan Transactional Outbox secara atomik
        try {
            $transaction = DB::transaction(function () use ($verifiedPayload, $transactionId, $orderId, $incomingMerchantId, $grossAmount, $currency, $paymentType, $mappedStatus, $providerEvent) {
                // Lock ulang di dalam transaksi DB untuk mencegah race condition webhook paralel
                $existing = InfaqTransaction::where('provider_transaction_id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $transactionTime = ! empty($verifiedPayload['transaction_time'])
                    ? Carbon::parse($verifiedPayload['transaction_time'], 'Asia/Jakarta')
                    : now('Asia/Jakarta');

                $settlementTime = ! empty($verifiedPayload['settlement_time'])
                    ? Carbon::parse($verifiedPayload['settlement_time'], 'Asia/Jakarta')
                    : null;

                $acquirer = $verifiedPayload['acquirer'] ?? null;

                $trx = InfaqTransaction::create([
                    'provider'                      => 'midtrans',
                    'provider_transaction_id'       => $transactionId,
                    'provider_order_id'             => $orderId,
                    'merchant_id'                   => $incomingMerchantId !== '' ? $incomingMerchantId : config('services.midtrans.merchant_id'),
                    'gross_amount'                  => (float) $grossAmount,
                    'currency'                      => $currency,
                    'payment_type'                  => $paymentType,
                    'transaction_status'            => $mappedStatus,
                    'review_status'                 => InfaqTransaction::REVIEW_NOT_REVIEWED,
                    'reconciliation_status'         => InfaqTransaction::RECON_NEEDS_REVIEW,
                    'transaction_time'              => $transactionTime,
                    'settlement_time'               => $settlementTime,
                    'acquirer'                      => $acquirer,
                    'issuer'                        => $verifiedPayload['issuer'] ?? null,
                    'merchant_cross_reference_id'   => $verifiedPayload['merchant_cross_reference_id'] ?? null,
                    'raw_payload'                   => $verifiedPayload,
                ]);

                // Jika SETTLEMENT: alokasikan ke pos default 'Belum Dialokasikan' & tulis ke Outbox
                if ($mappedStatus === InfaqTransaction::STATUS_SETTLEMENT) {
                    $defaultCategory = InfaqCategory::where('is_default', true)->first();

                    if ($defaultCategory) {
                        FundAllocation::create([
                            'infaq_transaction_id' => $trx->id,
                            'infaq_category_id'    => $defaultCategory->id,
                            'allocated_amount'     => $trx->gross_amount,
                            'allocated_by'         => null,
                            'notes'                => 'Alokasi otomatis sistem dari webhook QRIS Statis.',
                        ]);
                    }
                }

                $providerEvent->update(['processing_status' => 'PROCESSED']);

                return $trx;
            });
        } catch (UniqueConstraintViolationException) {
            $providerEvent->update(['processing_status' => 'IGNORED_DUPLICATE']);

            return [
                'http_status'    => 200,
                'status'         => 'duplicate_ignored',
                'message'        => 'Transaksi duplikat terdeteksi oleh constraint database.',
                'transaction_id' => $transactionId,
            ];
        }

        return [
            'http_status'    => 200,
            'status'         => 'processed',
            'message'        => 'Notifikasi pembayaran berhasil diproses.',
            'transaction_id' => $transaction->provider_transaction_id,
        ];
    }

    private function isValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
            $candidateKeys = array_filter([
            (string) config('services.midtrans.signature_key'),
            (string) config('services.midtrans.server_key'),
        ]);

        if ($candidateKeys === []) {
            return false;
        }

        $incomingSignature = strtolower($signatureKey);

        foreach ($candidateKeys as $key) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $key);
            if (hash_equals($expectedSignature, $incomingSignature)) {
                return true;
            }
        }

        return false;
    }

    private function mapTransactionStatus(string $rawStatus, ?string $fraudStatus): string
    {
        return match ($rawStatus) {
            'settlement' => InfaqTransaction::STATUS_SETTLEMENT,
            'capture'    => ($fraudStatus === null || strtolower($fraudStatus) === 'accept')
                ? InfaqTransaction::STATUS_SETTLEMENT
                : InfaqTransaction::STATUS_NEEDS_REVIEW,
            'deny'       => InfaqTransaction::STATUS_DENY,
            'cancel'     => InfaqTransaction::STATUS_CANCEL,
            'expire'     => InfaqTransaction::STATUS_EXPIRE,
            'failure'    => InfaqTransaction::STATUS_FAILURE,
            default      => InfaqTransaction::STATUS_NEEDS_REVIEW,
        };
    }

    private function sanitizeHeaders(array $headers): array
    {
        unset($headers['authorization'], $headers['cookie']);

        return $headers;
    }
}