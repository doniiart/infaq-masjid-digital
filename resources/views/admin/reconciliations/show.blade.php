<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800">Hasil Sesi Rekonsiliasi #{{ $reconciliation->id }}</h2>
            <a href="{{ route('admin.reconciliations.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Status Pencocokan</th>
                            <th class="px-4 py-3 text-left">Ref Eksternal</th>
                            <th class="px-4 py-3 text-right">Nominal Laporan</th>
                            <th class="px-4 py-3 text-right">Nominal Database</th>
                            <th class="px-4 py-3 text-left">Catatan &amp; Penyelesaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reconciliation->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-bold">{{ $item->match_status }}</td>
                                <td class="px-4 py-3 font-mono">{{ $item->external_transaction_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ $item->external_amount !== null ? 'Rp '.number_format($item->external_amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ $item->system_amount !== null ? 'Rp '.number_format($item->system_amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-600 mb-2">{{ $item->resolution_notes }}</p>
                                    @if($item->match_status !== 'MATCHED')
                                        <form method="POST" action="{{ route('admin.reconciliations.items.resolve', $item) }}" class="flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="resolution_notes" required placeholder="Tulis hasil tindak lanjut Takmir..." class="flex-1 rounded border-slate-300 text-xs">
                                            <button type="submit" class="px-3 py-1 bg-slate-800 text-white rounded font-semibold">Simpan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>