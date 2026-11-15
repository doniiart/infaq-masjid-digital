<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-xl text-slate-800">Manajemen Transaksi Infaq</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.transactions.export.csv', request()->query()) }}" class="px-3.5 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800">
                    Export Excel (CSV)
                </a>
                <a href="{{ route('admin.transactions.export.print', request()->query()) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900">
                    Cetak / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Form Filter Multi-Kriteria -->
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari ID Transaksi / Order</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="trx_... / order_..." class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Acquirer</label>
                <select name="acquirer" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Acquirer</option>
                    @foreach($acquirers as $acq)
                        <option value="{{ $acq }}" @selected(request('acquirer') === $acq)>{{ strtoupper($acq) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Minimum (Rp)</label>
                <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="0" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Maksimum (Rp)</label>
                <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="10000000" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pembayaran</label>
                <select name="transaction_status" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(['SETTLEMENT', 'FAILURE', 'DENY', 'CANCEL', 'EXPIRE', 'NEEDS_REVIEW'] as $st)
                        <option value="{{ $st }}" @selected(request('transaction_status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pemeriksaan Takmir</label>
                <select name="review_status" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua</option>
                    @foreach(['NOT_REVIEWED', 'VERIFIED', 'FLAGGED'] as $rv)
                        <option value="{{ $rv }}" @selected(request('review_status') === $rv)>{{ $rv }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2">
                <a href="{{ route('admin.transactions.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-medium">Reset</a>
                <button type="submit" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800">Terapkan Filter</button>
            </div>
        </form>

        <!-- Tabel Transaksi -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Transaction / Order ID</th>
                            <th class="px-4 py-3 text-left">Waktu (WIB)</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                            <th class="px-4 py-3 text-left">Acquirer / Issuer</th>
                            <th class="px-4 py-3 text-left">Alokasi Pos</th>
                            <th class="px-4 py-3 text-left">Status Bayar</th>
                            <th class="px-4 py-3 text-left">Rekonsiliasi</th>
                            <th class="px-4 py-3 text-left">Takmir</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono">
                                    <span class="font-bold text-slate-900 block">{{ $trx->provider_transaction_id }}</span>
                                    <span class="text-slate-500">{{ $trx->provider_order_id ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    <div>Trx: {{ $trx->transaction_time->timezone('Asia/Jakarta')->format('d/m/y H:i') }}</div>
                                    <div class="text-slate-400">Set: {{ $trx->settlement_time?->timezone('Asia/Jakarta')->format('d/m/y H:i') ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($trx->gross_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold uppercase text-slate-700">{{ $trx->acquirer ?? '-' }}</span>
                                    <span class="block text-slate-400">{{ $trx->issuer ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $trx->allocations->pluck('category.name')->filter()->join(', ') ?: 'Belum Dialokasikan' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded font-semibold {{ $trx->transaction_status === 'SETTLEMENT' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $trx->transaction_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-medium">{{ $trx->reconciliation_status }}</td>
                                <td class="px-4 py-3 text-slate-600 font-medium">{{ $trx->review_status }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.transactions.show', $trx) }}" class="text-emerald-700 font-semibold hover:underline">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">Tidak ada data transaksi yang sesuai dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>