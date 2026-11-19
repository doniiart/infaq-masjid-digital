<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    reconciliation: { type: Object, required: true },
});

const page = usePage();
const canManage = computed(() => ['super_admin', 'bendahara'].includes(page.props.auth?.user?.role));
const flashSuccess = computed(() => page.props.flash?.success || null);

const notesMap = reactive({});
const processingMap = reactive({});

const resolveItem = (item) => {
    const note = (notesMap[item.id] || '').trim();
    if (!note) return;

    processingMap[item.id] = true;
    router.patch(
        `/admin/reconciliations/items/${item.id}/resolve`,
        { resolution_notes: note },
        {
            preserveScroll: true,
            onSuccess: () => {
                notesMap[item.id] = '';
            },
            onFinish: () => {
                processingMap[item.id] = false;
            },
        }
    );
};

const formatRupiahOrDash = (val) => {
    if (val === null || val === undefined || val === '') return '-';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Hasil Sesi Rekonsiliasi #${reconciliation.id}`" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Hasil Sesi Rekonsiliasi #{{ reconciliation.id }}</h2>
            <Link href="/admin/reconciliations" class="text-sm text-slate-600 hover:underline">
                &larr; Kembali
            </Link>
        </template>

        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
            >
                {{ flashSuccess }}
            </div>

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
                            <tr v-for="item in reconciliation.items || []" :key="item.id" class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">
                                    {{ item.match_status }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700 whitespace-nowrap">
                                    {{ item.external_transaction_id || '-' }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap text-slate-700">
                                    {{ formatRupiahOrDash(item.external_amount) }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap text-slate-700">
                                    {{ formatRupiahOrDash(item.system_amount) }}
                                </td>
                                <td class="px-4 py-3">
                                    <p v-if="item.resolution_notes" class="text-slate-600 mb-2">
                                        {{ item.resolution_notes }}
                                    </p>
                                    <form
                                        v-if="canManage && item.match_status !== 'MATCHED'"
                                        @submit.prevent="resolveItem(item)"
                                        class="flex gap-2"
                                    >
                                        <input
                                            v-model="notesMap[item.id]"
                                            type="text"
                                            required
                                            placeholder="Tulis hasil tindak lanjut Takmir..."
                                            class="flex-1 rounded border-slate-300 text-xs"
                                        />
                                        <button
                                            type="submit"
                                            :disabled="processingMap[item.id]"
                                            class="px-3 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded font-semibold disabled:opacity-60"
                                        >
                                            Simpan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr v-if="!(reconciliation.items || []).length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Tidak ada item pada batch rekonsiliasi ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>