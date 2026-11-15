@extends('layouts.public')

@section('title', 'Informasi & Progress Pembangunan Masjid')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Rencana &amp; Realisasi</span>
        <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Informasi &amp; Progress Pembangunan</h1>
        <p class="text-slate-600 mt-2">Pantau target kebutuhan dana, persentase capaian, serta rincian penggunaan dana pembangunan.</p>
    </div>

    <!-- Kartu Capaian -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold">Target Anggaran</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($summary['development_target'], 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold">Dana Pembangunan Terkumpul</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($summary['development_collected'], 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold">Kekurangan Dana</p>
                @php $remaining = max(0, $summary['development_target'] - $summary['development_collected']); @endphp
                <p class="text-2xl font-extrabold text-amber-700 mt-1">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="w-full bg-slate-100 rounded-full h-5 overflow-hidden">
            <div class="bg-emerald-600 h-5 rounded-full text-xs text-white font-bold flex items-center justify-center" style="width: {{ max(6, $summary['progress_percent']) }}%">
                {{ $summary['progress_percent'] }}%
            </div>
        </div>
    </div>

    <!-- Riwayat Pengeluaran Khusus Pembangunan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Rincian Pengeluaran Pos Pembangunan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Keperluan</th>
                        <th class="px-6 py-3 text-left">Keterangan</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($developmentExpenses as $item)
                        <tr>
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $item->expense_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 font-semibold text-slate-900">{{ $item->title }}</td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $item->description ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-amber-700">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada pengeluaran pada kategori Infaq Pembangunan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection