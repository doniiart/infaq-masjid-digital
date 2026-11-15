<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-xl text-slate-800">Laporan Keuangan Periodik</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="px-3.5 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800">
                    Export Excel (CSV)
                </a>
                <a href="{{ route('admin.reports.export.print', request()->query()) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900">
                    Cetak / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-end gap-4 text-sm">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Periode</label>
                <select name="period" class="rounded-lg border-slate-300 text-sm">
                    <option value="daily" @selected($period === 'daily')>Harian (Hari Ini)</option>
                    <option value="weekly" @selected($period === 'weekly')>Mingguan (Minggu Ini)</option>
                    <option value="monthly" @selected($period === 'monthly')>Bulanan (Bulan Ini)</option>
                    <option value="yearly" @selected($period === 'yearly')>Tahunan (Tahun Ini)</option>
                    <option value="custom" @selected($period === 'custom')>Rentang Kustom</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mulai (Jika Kustom)</label>
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Selesai (Jika Kustom)</label>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="rounded-lg border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800">Tampilkan Laporan</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <p class="text-xs text-slate-500 uppercase font-semibold">Total Pemasukan Periode</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <p class="text-xs text-slate-500 uppercase font-semibold">Total Pengeluaran Periode</p>
                <p class="text-2xl font-extrabold text-amber-700 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <p class="text-xs text-slate-500 uppercase font-semibold">Surplus / Defisit Periode</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($netBalance, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Rekap Kategori ({{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }})</h3>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left">Kategori Pos</th>
                        <th class="px-5 py-3 text-right">Pemasukan</th>
                        <th class="px-5 py-3 text-right">Pengeluaran</th>
                        <th class="px-5 py-3 text-right">Selisih Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categorySummary as $row)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $row['name'] }}</td>
                            <td class="px-5 py-3 text-right text-emerald-700 font-semibold">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-amber-700 font-semibold">Rp {{ number_format($row['expense'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-slate-900 font-bold">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>