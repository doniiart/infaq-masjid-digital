<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Infaq QRIS Mingguan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; border-bottom: 2px solid #065f46; padding-bottom: 10px; margin-bottom: 16px; }
        .header h2 { margin: 0 0 4px 0; color: #065f46; font-size: 16px; }
        .header p { margin: 2px 0; color: #4b5563; font-size: 10px; }
        .summary-box { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; margin-bottom: 16px; }
        .summary-box table { width: 100%; border-collapse: collapse; }
        .summary-box td { padding: 3px 0; font-size: 11px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #065f46; color: #ffffff; padding: 7px 6px; text-align: left; font-size: 10px; }
        table.data td { border-bottom: 1px solid #e5e7eb; padding: 6px; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; font-size: 9px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN INFAQ QRIS MINGGUAN</h2>
        <p><strong>Masjid At Taqwa Candi Rejo / Gunung Rejo</strong></p>
        <p>Periode: {{ $periodLabel }} (Senin – Minggu)</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td><strong>Jumlah Transaksi Terverifikasi:</strong></td>
                <td class="text-right"><strong>{{ $transactions->count() }} Transaksi</strong></td>
            </tr>
            <tr>
                <td><strong>Total Infaq Minggu Ini (Settlement):</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($weeklyTotal, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td><strong>Total Akumulasi Kas Infaq Keseluruhan:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($overallTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 22%;">Waktu (WIB)</th>
                <th style="width: 28%;">Order ID</th>
                <th style="width: 15%;">Kanal</th>
                <th style="width: 12%;">Status</th>
                <th class="text-right" style="width: 18%; text-align: right;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->settlement_time ?? $trx->transaction_time)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                    <td>{{ $trx->provider_order_id }}</td>
                    <td>{{ strtoupper($trx->acquirer ?? $trx->payment_type ?? 'QRIS') }}</td>
                    <td>{{ $trx->transaction_status }}</td>
                    <td class="text-right">Rp {{ number_format((float) $trx->gross_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 16px; color: #6b7280;">
                        Belum ada transaksi infaq settlement pada periode Senin – Minggu ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Infaq Digital Masjid At Taqwa pada {{ $generatedAt }} WIB
    </div>
</body>
</html>