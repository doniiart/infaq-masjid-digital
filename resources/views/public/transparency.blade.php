@extends('layouts.public')

@section('title', 'Transparansi Dana & Laporan Keuangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Akuntabilitas Publik</span>
        <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Transparansi Dana &amp; Laporan Keuangan</h1>
        <p class="text-slate-600 mt-2">Rekapitulasi pemasukan infaq terverifikasi dan laporan pengeluaran masjid berdasarkan kategori pos dana.</p>
    </div>

    <!-- Rekap Per Kategori -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Rekapitulasi Saldo Per Kategori Dana</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Kategori Dana</th>
                        <th class="px-6 py-3 text-right">Dana Dialokasikan</th>
                        <th class="px-6 py-3 text-right">Pengeluaran</th>
                        <th class="px-6 py-3 text-right">Sisa Saldo Pos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categories as $cat)
                        <tr>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 block">{{ $cat['name'] }}</span>
                                <span class="text-xs text-slate-500">{{ $cat['description'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-700">Rp {{ number_format($cat['income'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-amber-700">Rp {{ number_format($cat['expense'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">Rp {{ number_format($cat['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daftar Pemasukan Terverifikasi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Riwayat Pemasukan Infaq (Status: Settlement)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Waktu Transaksi</th>
                        <th class="px-6 py-3 text-left">ID Referensi</th>
                        <th class="px-6 py-3 text-left">Kanal / Acquirer</th>
                        <th class="px-6 py-3 text-left">Alokasi Pos</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr>
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $trx->transaction_time->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td>
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-500">{{ substr($trx->provider_transaction_id, 0, 8) }}***</td>
                            <td class="px-6 py-3.5 uppercase text-slate-700">{{ $trx->acquirer ?? $trx->payment_type ?? 'QRIS' }}</td>
                            <td class="px-6 py-3.5 text-slate-600">
                                {{ $trx->allocations->pluck('category.name')->filter()->join(', ') ?: 'Belum Dialokasikan' }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-emerald-700">Rp {{ number_format($trx->gross_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada transaksi masuk yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $transactions->links() }}</div>
        @endif
    </div>

    <!-- Daftar Pengeluaran Dipublikasikan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Laporan Pengeluaran Dipublikasikan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Kategori</th>
                        <th class="px-6 py-3 text-left">Uraian Pengeluaran</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                        <tr>
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $exp->expense_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-slate-700">{{ $exp->category->name ?? '-' }}</td>
                            <td class="px-6 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $exp->title }}</span>
                                <span class="text-xs text-slate-500">{{ $exp->description }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-amber-700">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada data pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection