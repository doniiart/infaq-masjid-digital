<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800">Manajemen Pengeluaran &amp; Bukti Realisasi</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
        @endif

        <!-- Form Catat Pengeluaran -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">Catat Pengeluaran Baru</h3>
            <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Pos Anggaran</label>
                    <select name="infaq_category_id" required class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Pengeluaran</label>
                    <input type="date" name="expense_date" value="{{ now('Asia/Jakarta')->toDateString() }}" required class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal (Rp)</label>
                    <input type="number" name="amount" required min="1" placeholder="500000" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Keperluan</label>
                    <input type="text" name="title" required placeholder="Misal: Pembelian 50 Sak Semen" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan Detail</label>
                    <input type="text" name="description" placeholder="Nama toko / nomor nota" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Bukti Kuitansi (JPG/PNG/PDF, max 4MB)</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-slate-600">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>

        <!-- Riwayat Pengeluaran -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600 font-semibold">
                        <tr>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Kategori</th>
                            <th class="px-5 py-3 text-left">Keperluan</th>
                            <th class="px-5 py-3 text-left">Bukti</th>
                            <th class="px-5 py-3 text-right">Nominal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $exp)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">{{ $exp->expense_date->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ $exp->category->name ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-bold text-slate-900 block">{{ $exp->title }}</span>
                                    <span class="text-xs text-slate-500">{{ $exp->description }} &bull; Dicatat: {{ $exp->creator->name ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($exp->receipt_path)
                                        <a href="{{ asset('storage/' . $exp->receipt_path) }}" target="_blank" class="text-xs font-semibold text-emerald-700 hover:underline">Lihat Bukti</a>
                                    @else
                                        <span class="text-xs text-slate-400">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-amber-700">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if(Auth::user()->isSuperAdmin())
                                        <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" onsubmit="return confirm('Hapus catatan pengeluaran ini? Tindakan akan dicatat di Audit Log.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">Hapus: Super Admin</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada catatan pengeluaran.</td>
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
</x-app-layout>