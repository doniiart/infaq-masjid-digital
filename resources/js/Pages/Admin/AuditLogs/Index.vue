<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    logs: { type: Object, required: true },
});

const params = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');

const form = reactive({
    action: params.get('action') || '',
});

const filterLogs = () => {
    const query = form.action.trim() ? { action: form.action.trim() } : {};
    router.get('/admin/audit-logs', query, { preserveState: true, replace: true });
};

const formatDateTimeWib = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).formatToParts(new Date(val));

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')} ${get('hour')}:${get('minute')}:${get('second')}`;
};

const formatChanges = (log) => {
    const payload = log.new_values ?? log.old_values;
    if (!payload) return '-';
    return typeof payload === 'string' ? payload : JSON.stringify(payload);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Audit Log Aktivitas Administrator" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Audit Log Aktivitas Administrator</h2>
        </template>

        <div class="space-y-6">
            <form @submit.prevent="filterLogs" class="bg-white p-4 rounded-xl border border-slate-200 flex gap-3 text-sm">
                <input
                    v-model="form.action"
                    type="text"
                    placeholder="Filter aksi (misal: EXPENSE, TRANSACTION, AUTH)..."
                    class="flex-1 rounded-lg border-slate-300 text-sm"
                />
                <button
                    type="submit"
                    class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-semibold"
                >
                    Cari Log
                </button>
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
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ formatDateTimeWib(log.created_at) }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    {{ log.user?.name || 'Sistem' }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-emerald-800">
                                    {{ log.action }}
                                </td>
                                <td
                                    class="px-4 py-3 font-mono text-[11px] text-slate-600 max-w-md truncate"
                                    :title="formatChanges(log)"
                                >
                                    {{ formatChanges(log) }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ log.ip_address || '-' }}
                                </td>
                            </tr>
                            <tr v-if="!logs.data.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada rekaman audit log.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in logs.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            preserve-scroll
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition"
                            :class="
                                link.active
                                    ? 'bg-emerald-700 text-white border-emerald-700'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                            "
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs text-slate-400 border border-slate-100"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>