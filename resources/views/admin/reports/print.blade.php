<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Masjid</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
        th { background-color: #f1f5f9; }
        .right { text-align: right; }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Keuangan Infaq Pembangunan Masjid At Taqwa</h2>
    <p>Periode: <strong>{{ $startDate->format('d M Y') }} s/d {{ $endDate->format('d M Y') }}</strong></p>
    <p>Total Pemasukan: <strong>Rp {{ number_format($totalIncome, 0, ',', '.') }}</strong> | Total Pengeluaran: <strong>Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong> | Saldo Periode: <strong>Rp {{ number_format($netBalance, 0, ',', '.') }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>Kategori Pos</th>
                <th class="right">Pemasukan</th>
                <th class="right">Pengeluaran</th>
                <th class="right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorySummary as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="right">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['expense'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>