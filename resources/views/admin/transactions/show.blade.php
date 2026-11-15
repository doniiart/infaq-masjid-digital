<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800">Detail Transaksi: {{ $transaction->provider_transaction_id }}</h2>
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informasi Teknis Transaksi -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4 text-sm">
                <h3 class="font-bold text-slate-900 text-base border-b pb-2">Metadata Transaksi &amp; 3 Dimensi Status</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><dt class="text-xs text-slate-500">Provider Transaction ID</dt><dd class="font-mono font-bold">{{ $transaction->provider_transaction_id }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Provider Order ID</dt><dd class="font-mono">{{ $transaction->provider_order_id ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Merchant ID</dt><dd class="font-mono">{{ $transaction->merchant_id ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Nominal</dt><dd class="font-extrabold text-emerald-700 text-lg">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Metode &amp; Acquirer</dt><dd>{{ $transaction->payment_type }} / {{ $transaction->acquirer ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Issuer &amp; Cross Ref ID</dt><dd>{{ $transaction->issuer ?? '-' }} / {{ $transaction->merchant_cross_reference_id ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Waktu Transaksi</dt><dd>{{ $transaction->transaction_time->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</dd></div>
                    <div><dt class="text-xs text-slate-500">Waktu Settlement</dt><dd>{{ $transaction->settlement_time?->timezone('Asia/Jakarta')->format('d M Y, H:i:s') ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Status Pembayaran</dt><dd class="font-bold">{{ $transaction->transaction_status }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Status Rekonsiliasi</dt><dd class="font-bold">{{ $transaction->reconciliation_status }}</dd></div>
                </dl>

                <div class="pt-4">
                    <h4 class="text-xs font-bold uppercase text-slate-500 mb-2">Raw Payload Audit (JSONB)</h4>
                    <pre class="bg-slate-900 text-emerald-300 p-4 rounded-lg text-xs overflow-x-auto max-h-64">{{ json_encode($transaction->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>

            <!-- Panel Aksi Takmir (Pemeriksaan & Alokasi) -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-3">Pemeriksaan Administrasi Takmir</h3>
                    <form method="POST" action="{{ route('admin.transactions.review', $transaction) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="review_status" class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach(['NOT_REVIEWED', 'VERIFIED', 'FLAGGED'] as $rv)
                                <option value="{{ $rv }}" @selected($transaction->review_status === $rv)>{{ $rv }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900">
                            Simpan Status Pemeriksaan
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-3">Alokasi Pos Kategori Dana</h3>
                    <form method="POST" action="{{ route('admin.transactions.allocate', $transaction) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Kategori Pos</label>
                            <select name="infaq_category_id" class="w-full rounded-lg border-slate-300 text-sm">
                                @php $currentCatId = $transaction->allocations->first()?->infaq_category_id; @endphp
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected($currentCatId === $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Alokasi (Opsional)</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-300 text-sm">{{ $transaction->allocations->first()?->notes }}</textarea>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-700 text-white rounded-lg text-sm font-semibold hover:bg-emerald-800">
                            Simpan Alokasi Dana
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>