<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800">Rekonsiliasi Pembayaran Midtrans &amp; Acquirer Eksternal</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Ringkasan Status Rekonsiliasi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-sm">
            <div class="bg-white p-4 rounded-xl border border-slate-200"><p class="text-xs text-slate-500">Matched</p><p class="text-xl font-extrabold text-emerald-700">{{ $stats['matched'] }}</p></div>
            <div class="bg-white p-4 rounded-xl border border-slate-200"><p class="text-xs text-slate-500">Needs Review</p><p class="text-xl font-extrabold text-amber-600">{{ $stats['needs_review'] }}</p></div>
            <div class="bg-white p-4 rounded-xl border border-slate-200"><p class="text-xs text-slate-500">Selisih / Hilang</p><p class="text-xl font-extrabold text-red-600">{{ $stats['mismatch'] }}</p></div>
            <div class="bg-white p-4 rounded-xl border border-slate-200"><p class="text-xs text-slate-500">Luar Cakupan Midtrans</p><p class="text-xl font-extrabold text-indigo-700">{{ $stats['outside_scope'] }}</p></div>
            <div class="bg-white p-4 rounded-xl border border-slate-200"><p class="text-xs text-slate-500">Sinkronisasi Terakhir</p><p class="text-xs font-bold text-slate-800 mt-1">{{ $stats['last_sync']?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? 'Belum Pernah' }}</p></div>
        </div>

        <!-- Form Impor Rekonsiliasi Manual -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-2">Jalankan Rekonsiliasi Manual (Impor Laporan Resmi CSV)</h3>
            <p class="text-xs text-slate-500 mb-4">Format kolom CSV yang didukung: <code>transaction_id, order_id, gross_amount, acquirer</code>.</p>
            <form method="POST" action="{{ route('admin.reconciliations.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sumber Laporan</label>
                    <select name="source_type" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="midtrans_report">Midtrans Transaction Report</option>
                        <option value="external_bank_statement">Mutasi Bank / Acquirer Eksternal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Mulai</label>
                    <input type="date" name="period_start" required class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Selesai</label>
                    <input type="date" name="period_end" required class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">File CSV Laporan</label>
                    <input type="file" name="report_file" accept=".csv,.txt" required class="w-full text-xs text-slate-600">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800">Proses Rekonsiliasi</button>
                </div>
            </form>
        </div>

        <!-- Riwayat Sesi Rekonsiliasi -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left">Waktu Eksekusi</th>
                        <th class="px-5 py-3 text-left">Sumber</th>
                        <th class="px-5 py-3 text-left">Periode</th>
                        <th class="px-5 py-3 text-center">Cocok</th>
                        <th class="px-5 py-3 text-center">Selisih / Review</th>
                        <th class="px-5 py-3 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($runs as $run)
                        <tr>
                            <td class="px-5 py-3 text-slate-600">{{ $run->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $run->source_type }}</td>
                            <td class="px-5 py-3 text-xs text-slate-500">{{ $run->period_start->format('d/m/y') }} - {{ $run->period_end->format('d/m/y') }}</td>
                            <td class="px-5 py-3 text-center font-bold text-emerald-700">{{ $run->total_matched }}</td>
                            <td class="px-5 py-3 text-center font-bold text-amber-700">{{ $run->total_discrepancies }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.reconciliations.show', $run) }}" class="text-emerald-700 font-semibold hover:underline">Lihat Hasil</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada riwayat rekonsiliasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>