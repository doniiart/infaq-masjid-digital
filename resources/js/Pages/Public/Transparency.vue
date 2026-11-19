<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    transactions: { type: [Array, Object], default: () => [] },
    expenses: { type: [Array, Object], default: () => [] },
});

const transactionRows = computed(() =>
    Array.isArray(props.transactions) ? props.transactions : (props.transactions?.data || [])
);

const transactionLinks = computed(() =>
    !Array.isArray(props.transactions) && Array.isArray(props.transactions?.links) ? props.transactions.links : []
);

const expenseRows = computed(() =>
    Array.isArray(props.expenses) ? props.expenses : (props.expenses?.data || [])
);

const expenseLinks = computed(() =>
    !Array.isArray(props.expenses) && Array.isArray(props.expenses?.links) ? props.expenses.links : []
);

const formatRupiah = (val) => 'Rp ' + Math.round(Number(val || 0)).toLocaleString('id-ID');

const formatDateTimeWib = (val) => {
    if (!val) return '-';
    const str = String(val);
    if (str.includes('WIB')) return str;

    const parsed = new Date(str);
    if (Number.isNaN(parsed.getTime())) return `${str} WIB`;

    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).formatToParts(parsed);

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}, ${get('hour')}:${get('minute')} WIB`;
};

const formatDate = (val) => {
    if (!val) return '-';
    const str = String(val);
    const parsed = new Date(str);
    if (Number.isNaN(parsed.getTime())) return str;

    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).formatToParts(parsed);

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}`;
};

const formatReference = (trx) => {
    if (trx.reference) return trx.reference;
    if (trx.provider_transaction_id) return `${String(trx.provider_transaction_id).slice(0, 8)}***`;
    return '-';
};

const formatAllocations = (trx) => {
    if (Array.isArray(trx.categories) && trx.categories.length) {
        return trx.categories.join(', ');
    }
    if (Array.isArray(trx.allocations) && trx.allocations.length) {
        const names = trx.allocations.map((a) => a.category?.name).filter(Boolean);
        if (names.length) return names.join(', ');
    }
    return 'Belum Dialokasikan';
};
</script>

<template>
    <PublicLayout>
        <Head title="Transparansi Dana & Laporan Keuangan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Akuntabilitas Publik</span>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Transparansi Dana &amp; Laporan Keuangan</h1>
                <p class="text-slate-600 mt-2">
                    Rekapitulasi pemasukan infaq terverifikasi dan laporan pengeluaran masjid berdasarkan kategori pos dana.
                </p>
            </div>

            <!-- Rekap Per Kategori -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Rekapitulasi Saldo Per Kategori Dana</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Kategori Dana</th>
                                <th class="px-6 py-3 text-right">Dana Dialokasikan</th>
                                <th class="px-6 py-3 text-right">Pengeluaran</th>
                                <th class="px-6 py-3 text-right">Sisa Saldo Pos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(cat, idx) in categories" :key="cat.id || idx">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 block">{{ cat.name }}</span>
                                    <span v-if="cat.description" class="text-xs text-slate-500">{{ cat.description }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-emerald-700 whitespace-nowrap">
                                    {{ formatRupiah(cat.income) }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-amber-700 whitespace-nowrap">
                                    {{ formatRupiah(cat.expense) }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                    {{ formatRupiah(cat.balance) }}
                                </td>
                            </tr>
                            <tr v-if="!categories.length">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    Belum ada data kategori dana.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Daftar Pemasukan Terverifikasi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Riwayat Pemasukan Infaq (Status: Settlement)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Waktu Transaksi</th>
                                <th class="px-6 py-3 text-left">ID Referensi</th>
                                <th class="px-6 py-3 text-left">Kanal / Acquirer</th>
                                <th class="px-6 py-3 text-left">Alokasi Pos</th>
                                <th class="px-6 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="trx in transactionRows" :key="trx.id">
                                <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ formatDateTimeWib(trx.transaction_time) }}
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs text-slate-500">
                                    {{ formatReference(trx) }}
                                </td>
                                <td class="px-6 py-3.5 uppercase text-slate-700">
                                    {{ trx.acquirer || trx.payment_type || 'QRIS' }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-600">
                                    {{ formatAllocations(trx) }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-emerald-700 whitespace-nowrap">
                                    {{ formatRupiah(trx.gross_amount) }}
                                </td>
                            </tr>
                            <tr v-if="!transactionRows.length">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    Belum ada transaksi masuk yang tercatat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="transactionLinks.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in transactionLinks" :key="idx">
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

            <!-- Daftar Pengeluaran Dipublikasikan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Laporan Pengeluaran Dipublikasikan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Tanggal</th>
                                <th class="px-6 py-3 text-left">Kategori</th>
                                <th class="px-6 py-3 text-left">Uraian Pengeluaran</th>
                                <th class="px-6 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="exp in expenseRows" :key="exp.id">
                                <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ formatDate(exp.expense_date) }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-700">
                                    {{ exp.category?.name || exp.category_name || '-' }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="font-semibold text-slate-900 block">
                                        {{ exp.title || exp.description }}
                                    </span>
                                    <span v-if="exp.title && exp.description" class="text-xs text-slate-500">
                                        {{ exp.description }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-amber-700 whitespace-nowrap">
                                    {{ formatRupiah(exp.amount) }}
                                </td>
                            </tr>
                            <tr v-if="!expenseRows.length">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    Belum ada data pengeluaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="expenseLinks.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in expenseLinks" :key="idx">
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
    </PublicLayout>
</template>