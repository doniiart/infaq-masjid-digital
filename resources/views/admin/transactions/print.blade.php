<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Daftar Transaksi Infaq</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; }
        .right { text-align: right; }
    </style>
</head>
<body onload="window.print()">
    <h2>Daftar Transaksi Infaq Digital — Masjid At Taqwa</h2>
    <p>Dicetak pada: {{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Waktu (WIB)</th>
                <th>Acquirer</th>
                <th>Pos Alokasi</th>
                <th>Status</th>
                <th class="right">Nominal (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td>{{ $trx->provider_transaction_id }}</td>
                    <td>{{ $trx->transaction_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                    <td>{{ strtoupper($trx->acquirer ?? '-') }}</td>
                    <td>{{ $trx->allocations->pluck('category.name')->filter()->join(', ') ?: 'Belum Dialokasikan' }}</td>
                    <td>{{ $trx->transaction_status }}</td>
                    <td class="right">Rp {{ number_format($trx->gross_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>