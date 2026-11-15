<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800">Manajemen Kategori &amp; Pos Anggaran Infaq</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
        @endif

        <!-- Tambah Kategori Baru -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">Tambah Kategori Pos Dana Baru</h3>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Misal: Infaq Pendidikan TPQ" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Anggaran (Opsional)</label>
                    <input type="number" name="target_amount" placeholder="0" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
                    <input type="text" name="description" placeholder="Deskripsi singkat peruntukan" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800">Tambah Kategori</button>
                </div>
            </form>
        </div>

        <!-- Daftar Kategori -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left">Nama &amp; Keterangan</th>
                        <th class="px-5 py-3 text-left">Target Dana</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Simpan Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categories as $cat)
                        <tr>
                            <form method="POST" action="{{ route('admin.categories.update', $cat) }}">
                                @csrf
                                @method('PUT')
                                <td class="px-5 py-3 space-y-1">
                                    <input type="text" name="name" value="{{ $cat->name }}" class="w-full rounded border-slate-300 text-sm font-semibold">
                                    <input type="text" name="description" value="{{ $cat->description }}" class="w-full rounded border-slate-200 text-xs text-slate-600">
                                    @if($cat->is_default)
                                        <span class="inline-block text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Pos Default Transaksi QRIS Otomatis</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <input type="number" name="target_amount" value="{{ $cat->target_amount }}" class="w-40 rounded border-slate-300 text-sm">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <label class="inline-flex items-center gap-1.5 text-xs">
                                        <input type="checkbox" name="is_active" value="1" @checked($cat->is_active) @disabled($cat->is_default)>
                                        <span>Aktif</span>
                                    </label>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button type="submit" class="px-3.5 py-1.5 bg-slate-800 text-white rounded text-xs font-semibold hover:bg-slate-900">Update</button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>