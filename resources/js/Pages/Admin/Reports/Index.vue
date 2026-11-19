<script setup>
import { computed, reactive, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    period: { type: String, default: 'monthly' },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
    totalIncome: { type: [Number, String], default: 0 },
    totalExpense: { type: [Number, String], default: 0 },
    netBalance: { type: [Number, String], default: 0 },
    categorySummary: { type: Array, default: () => [] },
});

const toInputDate = (val) => {
    if (!val) {
        return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta' }).format(new Date());
    }
    const str = String(val);
    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
    return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta' }).format(new Date(str));
};

const formatDisplayDate = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).formatToParts(new Date(val));
    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}`;
};

const form = reactive({
    period: props.period || 'monthly',
    start_date: toInputDate(props.startDate),
    end_date: toInputDate(props.endDate),
});

watch(
    () => [props.period, props.startDate, props.endDate],
    ([newPeriod, newStart, newEnd]) => {
        if (newPeriod) form.period = newPeriod;
        if (newStart) form.start_date = toInputDate(newStart);
        if (newEnd) form.end_date = toInputDate(newEnd);
    }
);

const applyPeriod = () => {
    router.get('/admin/reports', form, { preserveState: true, replace: true });
};

const queryString = computed(() => {
    const params = new URLSearchParams({
        period: form.period,
        start_date: form.start_date,
        end_date: form.end_date,
    });
    return params.toString();
});

const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Keuangan Periodik" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Laporan Keuangan Periodik</h2>
            <div class="flex gap-2">
                <a
                    :href="`/admin/reports/export/csv?${queryString}`"
                    class="px-3.5 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800"
                >
                    Export Excel (CSV)
                </a>
                <a
                    :href="`/admin/reports/export/print?${queryString}`"
                    target="_blank"
                    class="px-3.5 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900"
                >
                    Cetak / PDF
                </a>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Form Filter Periode -->
            <form
                @submit.prevent="applyPeriod"
                class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-end gap-4 text-sm"
            >
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Periode</label>
                    <select v-model="form.period" class="rounded-lg border-slate-300 text-sm">
                        <option value="daily">Harian (Hari Ini)</option>
                        <option value="weekly">Mingguan (Minggu Ini)</option>
                        <option value="monthly">Bulanan (Bulan Ini)</option>
                        <option value="yearly">Tahunan (Tahun Ini)</option>
                        <option value="custom">Rentang Kustom</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mulai (Jika Kustom)</label>
                    <input v-model="form.start_date" type="date" class="rounded-lg border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Selesai (Jika Kustom)</label>
                    <input v-model="form.end_date" type="date" class="rounded-lg border-slate-300 text-sm" />
                </div>
                <button
                    type="submit"
                    class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800"
                >
                    Tampilkan Laporan
                </button>
            </form>

            <!-- Ringkasan Periode -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500 uppercase font-semibold">Total Pemasukan Periode</p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ formatRupiah(totalIncome) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500 uppercase font-semibold">Total Pengeluaran Periode</p>
                    <p class="text-2xl font-extrabold text-amber-700 mt-1">{{ formatRupiah(totalExpense) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-500 uppercase font-semibold">Surplus / Defisit Periode</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ formatRupiah(netBalance) }}</p>
                </div>
            </div>

            <!-- Tabel Rekap Kategori -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">
                        Rekap Kategori ({{ formatDisplayDate(startDate) }} &ndash; {{ formatDisplayDate(endDate) }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-5 py-3 text-left">Kategori Pos</th>
                                <th class="px-5 py-3 text-right">Pemasukan</th>
                                <th class="px-5 py-3 text-right">Pengeluaran</th>
                                <th class="px-5 py-3 text-right">Selisih Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(row, idx) in categorySummary" :key="row.id || idx" class="hover:bg-slate-50/50">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ row.name }}</td>
                                <td class="px-5 py-3 text-right text-emerald-700 font-semibold">
                                    {{ formatRupiah(row.income) }}
                                </td>
                                <td class="px-5 py-3 text-right text-amber-700 font-semibold">
                                    {{ formatRupiah(row.expense) }}
                                </td>
                                <td class="px-5 py-3 text-right text-slate-900 font-bold">
                                    {{ formatRupiah(row.balance) }}
                                </td>
                            </tr>
                            <tr v-if="!categorySummary.length">
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500 text-xs">
                                    Tidak ada data rekap kategori pada periode ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>