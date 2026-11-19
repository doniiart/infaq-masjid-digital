<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    runs: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const canManage = computed(() => ['super_admin', 'bendahara'].includes(page.props.auth?.user?.role));
const flashSuccess = computed(() => page.props.flash?.success || null);
const flashError = computed(() => page.props.flash?.error || null);

const fileInput = ref(null);

const form = useForm({
    source_type: 'midtrans_report',
    period_start: '',
    period_end: '',
    report_file: null,
});

const handleFileChange = (event) => {
    form.report_file = event.target.files?.[0] || null;
};

const submitImport = () => {
    form.post('/admin/reconciliations/import', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('period_start', 'period_end', 'report_file');
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
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
        hour12: false,
    }).formatToParts(new Date(val));
    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')} ${get('hour')}:${get('minute')}`;
};

const formatShortDate = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: '2-digit',
        year: '2-digit',
    }).formatToParts(new Date(val));
    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')}/${get('month')}/${get('year')}`;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Rekonsiliasi Pembayaran Midtrans & Acquirer Eksternal" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Rekonsiliasi Pembayaran Midtrans &amp; Acquirer Eksternal</h2>
        </template>

        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm font-medium"
            >
                {{ flashError }}
            </div>

            <!-- Ringkasan Status Rekonsiliasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-sm">
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500">Matched</p>
                    <p class="text-xl font-extrabold text-emerald-700">{{ stats?.matched ?? 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500">Needs Review</p>
                    <p class="text-xl font-extrabold text-amber-600">{{ stats?.needs_review ?? 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500">Selisih / Hilang</p>
                    <p class="text-xl font-extrabold text-red-600">{{ stats?.mismatch ?? 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500">Luar Cakupan Midtrans</p>
                    <p class="text-xl font-extrabold text-indigo-700">{{ stats?.outside_scope ?? 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500">Sinkronisasi Terakhir</p>
                    <p class="text-xs font-bold text-slate-800 mt-1">
                        {{ formatDateTimeWib(stats?.last_sync) }}
                    </p>
                </div>
            </div>

            <!-- Form Impor Rekonsiliasi Manual -->
            <div v-if="canManage" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 mb-2">Jalankan Rekonsiliasi Manual (Impor Laporan Resmi CSV)</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Format kolom CSV yang didukung:
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">transaction_id, order_id, gross_amount, acquirer</code>.
                </p>

                <form @submit.prevent="submitImport" class="grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sumber Laporan</label>
                        <select v-model="form.source_type" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="midtrans_report">Midtrans Transaction Report</option>
                            <option value="external_bank_statement">Mutasi Bank / Acquirer Eksternal</option>
                        </select>
                        <p v-if="form.errors.source_type" class="text-xs text-red-600 mt-1">
                            {{ form.errors.source_type }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Mulai</label>
                        <input
                            v-model="form.period_start"
                            type="date"
                            required
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.period_start" class="text-xs text-red-600 mt-1">
                            {{ form.errors.period_start }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Selesai</label>
                        <input
                            v-model="form.period_end"
                            type="date"
                            required
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.period_end" class="text-xs text-red-600 mt-1">
                            {{ form.errors.period_end }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">File CSV Laporan</label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".csv,.txt"
                            required
                            @change="handleFileChange"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                        />
                        <p v-if="form.errors.report_file" class="text-xs text-red-600 mt-1">
                            {{ form.errors.report_file }}
                        </p>
                    </div>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800 disabled:opacity-60"
                        >
                            Proses Rekonsiliasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Riwayat Sesi Rekonsiliasi -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-5 py-3 text-left">Waktu Eksekusi</th>
                                <th class="px-5 py-3 text-left">Sumber</th>
                                <th class="px-5 py-3 text-left">Periode</th>
                                <th class="px-5 py-3 text-center">Cocok</th>
                                <th class="px-5 py-3 text-center">Selisih / Review</th>
                                <th class="px-5 py-3 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="run in runs.data" :key="run.id" class="hover:bg-slate-50/50">
                                <td class="px-5 py-3 text-slate-600 whitespace-nowrap">
                                    {{ formatDateTimeWib(run.created_at) }}
                                </td>
                                <td class="px-5 py-3 font-semibold text-slate-700">
                                    {{ run.source_type }}
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                                    {{ formatShortDate(run.period_start) }} - {{ formatShortDate(run.period_end) }}
                                </td>
                                <td class="px-5 py-3 text-center font-bold text-emerald-700">
                                    {{ run.total_matched ?? 0 }}
                                </td>
                                <td class="px-5 py-3 text-center font-bold text-amber-700">
                                    {{ run.total_discrepancies ?? 0 }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="`/admin/reconciliations/${run.id}`"
                                        class="text-emerald-700 font-semibold hover:underline"
                                    >
                                        Lihat Hasil
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!runs.data.length">
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                    Belum ada riwayat rekonsiliasi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="runs.links && runs.links.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in runs.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            preserve-scroll
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition"
                            :class="link.active ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
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