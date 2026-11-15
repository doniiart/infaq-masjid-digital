<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Ringkasan Keuangan &amp; Operasional Takmir
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Kartu KPI Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Total Infaq Masuk (Settlement)</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ number_format($settledCount) }} transaksi settlement total</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Dana Pos Pembangunan</p>
                <p class="text-2xl font-extrabold text-teal-700 mt-1">Rp {{ number_format($developmentFund, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Teralokasi khusus pembangunan</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Total Pengeluaran</p>
                <p class="text-2xl font-extrabold text-amber-700 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Seluruh pos anggaran</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Saldo Kas Bersih</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Hari ini: <strong>{{ $todayCount }}</strong> trx &bull; Bulan ini: <strong>{{ $monthCount }}</strong> trx</p>
            </div>
        </div>

        <!-- Grafik Transaksi Harian & Pemasukan Bulanan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-4">Grafik Infaq 7 Hari Terakhir</h3>
                @php $maxDaily = max(1, collect($dailyChart)->max('amount')); @endphp
                <div class="grid grid-cols-7 gap-2 items-end h-44 pt-6 border-b border-slate-100">
                    @foreach($dailyChart as $bar)
                        @php $height = max(6, round(($bar['amount'] / $maxDaily) * 100)); @endphp
                        <div class="flex flex-col items-center justify-end h-full">
                            <span class="text-[10px] text-slate-500 mb-1">{{ $bar['amount'] > 0 ? number_format($bar['amount']/1000, 0).'k' : '0' }}</span>
                            <div class="w-full max-w-[32px] bg-emerald-600 rounded-t" style="height: {{ $height }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-2 text-center text-[11px] text-slate-600 mt-2">
                    @foreach($dailyChart as $bar)
                        <span>{{ $bar['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-4">Pemasukan vs Pengeluaran (6 Bulan Terakhir)</h3>
                <div class="space-y-3">
                    @foreach($monthlyChart as $m)
                        <div class="text-xs">
                            <div class="flex justify-between font-medium text-slate-700 mb-1">
                                <span>{{ $m['label'] }}</span>
                                <span>Masuk: <strong class="text-emerald-700">Rp {{ number_format($m['income'], 0, ',', '.') }}</strong> | Keluar: <strong class="text-amber-700">Rp {{ number_format($m['expense'], 0, ',', '.') }}</strong></span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex">
                                @php
                                    $sumPair = max(1, $m['income'] + $m['expense']);
                                    $incPct = round(($m['income'] / $sumPair) * 100);
                                    $expPct = round(($m['expense'] / $sumPair) * 100);
                                @endphp
                                <div class="bg-emerald-600 h-2" style="width: {{ $incPct }}%"></div>
                                <div class="bg-amber-500 h-2" style="width: {{ $expPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Transaksi Terbaru & Aktivitas Audit Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800">Transaksi Masuk Terbaru</h3>
                    <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">Kelola Semua &rarr;</a>
                </div>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse($recentTransactions as $trx)
                        <div class="py-2.5 flex justify-between items-center">
                            <div>
                                <a href="{{ route('admin.transactions.show', $trx) }}" class="font-semibold text-slate-800 hover:text-emerald-700">
                                    {{ $trx->provider_transaction_id }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $trx->transaction_time->timezone('Asia/Jakarta')->format('d M Y H:i') }} &bull; {{ $trx->transaction_status }}</p>
                            </div>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($trx->gross_amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-500 text-sm">Belum ada transaksi masuk.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800">Aktivitas Takmir Terbaru (Audit Log)</h3>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">Semua Log &rarr;</a>
                </div>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse($recentActivities as $log)
                        <div class="py-2.5 flex justify-between items-start gap-2">
                            <div>
                                <span class="font-mono text-xs font-semibold text-slate-800">{{ $log->action }}</span>
                                <p class="text-xs text-slate-500">Oleh: {{ $log->user->name ?? 'Sistem' }} ({{ $log->ip_address }})</p>
                            </div>
                            <span class="text-xs text-slate-400 whitespace-nowrap">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M H:i') }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-500 text-sm">Belum ada log aktivitas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>