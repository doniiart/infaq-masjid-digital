<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800">Audit Log Aktivitas Administrator</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 flex gap-3 text-sm">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter aksi (misal: EXPENSE, TRANSACTION, AUTH)..." class="flex-1 rounded-lg border-slate-300 text-sm">
            <button type="submit" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold">Cari Log</button>
        </form>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu (WIB)</th>
                            <th class="px-4 py-3 text-left">Pengurus</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                            <th class="px-4 py-3 text-left">Perubahan Data</th>
                            <th class="px-4 py-3 text-left">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->user->name ?? 'Sistem' }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-emerald-800">{{ $log->action }}</td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-600 max-w-md truncate">
                                    {{ json_encode($log->new_values ?? $log->old_values, JSON_UNESCAPED_SLASHES) }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada rekaman audit log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>