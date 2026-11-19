<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transactions: { type: Object, required: true },
    acquirers: { type: Array, default: () => [] },
});

const params = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');

const form = reactive({
    search: params.get('search') || '',
    date_from: params.get('date_from') || '',
    date_to: params.get('date_to') || '',
    acquirer: params.get('acquirer') || '',
    min_amount: params.get('min_amount') || '',
    max_amount: params.get('max_amount') || '',
    transaction_status: params.get('transaction_status') || '',
    review_status: params.get('review_status') || '',
});

const applyFilter = () => {
    const cleanQuery = Object.fromEntries(
        Object.entries(form).filter(([, val]) => val !== '' && val !== null)
    );
    router.get('/admin/transactions', cleanQuery, { preserveState: true, replace: true });
};

const resetFilter = () => {
    Object.keys(form).forEach((k) => (form[k] = ''));
    router.get('/admin/transactions', {}, { preserveState: true, replace: true });
};

const activeQueryString = computed(() => {
    const query = new URLSearchParams();
    Object.entries(form).forEach(([key, val]) => {
        if (val !== '' && val !== null) {
            query.set(key, val);
        }
    });
    const str = query.toString();
    return str ? `?${str}` : '';
});

const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

const formatDateTimeWib = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: '2-digit',
        year: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).formatToParts(new Date(val));

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')}/${get('month')}/${get('year')} ${get('hour')}:${get('minute')}`;
};

const allocationNames = (tx) => {
    const names = (tx.allocations || [])
        .map((a) => a.category?.name)
        .filter(Boolean);
    return names.length ? names.join(', ') : 'Belum Dialokasikan';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Transaksi Infaq" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Manajemen Transaksi Infaq</h2>
            <div class="flex gap-2">
                <a
                    :href="`/admin/transactions/export/csv${activeQueryString}`"
                    class="px-3.5 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800"
                >
                    Export Excel (CSV)
                </a>
                <a
                    :href="`/admin/transactions/export/print${activeQueryString}`"
                    target="_blank"
                    class="px-3.5 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900"
                >
                    Cetak / PDF
                </a>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Form Filter Multi-Kriteria -->
            <form
                @submit.prevent="applyFilter"
                class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm"
            >
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Cari ID Transaksi / Order</label>
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="trx_... / order_..."
                        class="w-full rounded-lg border-slate-300 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                    <input v-model="form.date_from" type="date" class="w-full rounded-lg border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input v-model="form.date_to" type="date" class="w-full rounded-lg border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Acquirer</label>
                    <select v-model="form.acquirer" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Semua Acquirer</option>
                        <option v-for="acq in acquirers" :key="acq" :value="acq">
                            {{ String(acq).toUpperCase() }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Minimum (Rp)</label>
                    <input
                        v-model="form.min_amount"
                        type="number"
                        placeholder="0"
                        class="w-full rounded-lg border-slate-300 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Maksimum (Rp)</label>
                    <input
                        v-model="form.max_amount"
                        type="number"
                        placeholder="10000000"
                        class="w-full rounded-lg border-slate-300 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pembayaran</label>
                    <select v-model="form.transaction_status" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Semua Status</option>
                        <option
                            v-for="st in ['SETTLEMENT', 'FAILURE', 'DENY', 'CANCEL', 'EXPIRE', 'NEEDS_REVIEW']"
                            :key="st"
                            :value="st"
                        >
                            {{ st }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pemeriksaan Takmir</label>
                    <select v-model="form.review_status" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Semua</option>
                        <option v-for="rv in ['NOT_REVIEWED', 'VERIFIED', 'FLAGGED']" :key="rv" :value="rv">
                            {{ rv }}
                        </option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="resetFilter"
                        class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-medium hover:bg-slate-50"
                    >
                        Reset
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </form>

            <!-- Tabel Transaksi -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Transaction / Order ID</th>
                                <th class="px-4 py-3 text-left">Waktu (WIB)</th>
                                <th class="px-4 py-3 text-right">Nominal</th>
                                <th class="px-4 py-3 text-left">Acquirer / Issuer</th>
                                <th class="px-4 py-3 text-left">Alokasi Pos</th>
                                <th class="px-4 py-3 text-left">Status Bayar</th>
                                <th class="px-4 py-3 text-left">Rekonsiliasi</th>
                                <th class="px-4 py-3 text-left">Takmir</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono">
                                    <span class="font-bold text-slate-900 block">{{ tx.provider_transaction_id }}</span>
                                    <span class="text-slate-500">{{ tx.provider_order_id || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    <div>Trx: {{ formatDateTimeWib(tx.transaction_time) }}</div>
                                    <div class="text-slate-400">
                                        Set: {{ tx.settlement_time ? formatDateTimeWib(tx.settlement_time) : '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900 whitespace-nowrap">
                                    {{ formatRupiah(tx.gross_amount) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold uppercase text-slate-700">{{ tx.acquirer || '-' }}</span>
                                    <span class="block text-slate-400">{{ tx.issuer || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ allocationNames(tx) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-0.5 rounded font-semibold"
                                        :class="
                                            tx.transaction_status === 'SETTLEMENT'
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : 'bg-amber-100 text-amber-800'
                                        "
                                    >
                                        {{ tx.transaction_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-medium">
                                    {{ tx.reconciliation_status }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-medium">
                                    {{ tx.review_status }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="`/admin/transactions/${tx.id}`"
                                        class="text-emerald-700 font-semibold hover:underline"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!transactions.data.length">
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                    Tidak ada data transaksi yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="transactions.links && transactions.links.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in transactions.links" :key="idx">
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