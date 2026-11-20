<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
});

const totalAmount = computed(() =>
    props.transactions.reduce((sum, trx) => sum + Number(trx.gross_amount || 0), 0)
);

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(value || 0));

const formatDateTime = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handlePrint = () => window.print();

onMounted(() => {
    setTimeout(() => window.print(), 400);
});
</script>

<template>
    <Head title="Cetak Laporan Transaksi Infaq" />

    <div class="min-h-screen bg-white p-8 text-gray-900">
        <div class="mx-auto max-w-5xl">
            <!-- Tombol aksi (disembunyikan saat print) -->
            <div class="mb-6 flex items-center justify-between border-b pb-4 print:hidden">
                <a
                    href="/admin/transactions"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    &larr; Kembali ke Transaksi
                </a>
                <button
                    type="button"
                    @click="handlePrint"
                    class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                >
                    Cetak / Simpan PDF
                </button>
            </div>

            <!-- Kop Laporan -->
            <div class="mb-6 border-b-2 border-emerald-800 pb-4 text-center">
                <h1 class="text-xl font-bold uppercase tracking-wide text-emerald-900">
                    Laporan Transaksi Infaq QRIS
                </h1>
                <p class="mt-1 text-sm font-medium text-gray-700">
                    Masjid At Taqwa Candi Rejo / Gunung Rejo
                </p>
                <p class="mt-0.5 text-xs text-gray-500">
                    Dicetak pada {{ formatDateTime(new Date().toISOString()) }} WIB
                </p>
            </div>

            <!-- Ringkasan -->
            <div class="mb-6 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm">
                <span>Total Data: <strong>{{ transactions.length }} transaksi</strong></span>
                <span>Total Nominal: <strong class="text-emerald-900">{{ formatCurrency(totalAmount) }}</strong></span>
            </div>

            <!-- Tabel Transaksi -->
            <table class="w-full border-collapse text-left text-xs">
                <thead>
                    <tr style="background-color: #065f46; color: #ffffff;">
                        <th class="border border-emerald-800 px-3 py-2">No</th>
                        <th class="border border-emerald-800 px-3 py-2">Order ID</th>
                        <th class="border border-emerald-800 px-3 py-2">Waktu (WIB)</th>
                        <th class="border border-emerald-800 px-3 py-2">Kanal</th>
                        <th class="border border-emerald-800 px-3 py-2">Status Bayar</th>
                        <th class="border border-emerald-800 px-3 py-2">Takmir</th>
                        <th class="border border-emerald-800 px-3 py-2 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(trx, index) in transactions"
                        :key="trx.id"
                        class="odd:bg-white even:bg-gray-50"
                    >
                        <td class="border border-gray-200 px-3 py-2">{{ index + 1 }}</td>
                        <td class="border border-gray-200 px-3 py-2 font-mono">{{ trx.provider_order_id }}</td>
                        <td class="border border-gray-200 px-3 py-2">{{ formatDateTime(trx.transaction_time) }}</td>
                        <td class="border border-gray-200 px-3 py-2 uppercase">{{ trx.acquirer || trx.payment_type || 'QRIS' }}</td>
                        <td class="border border-gray-200 px-3 py-2">{{ trx.transaction_status }}</td>
                        <td class="border border-gray-200 px-3 py-2">{{ trx.review_status }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-right font-semibold">
                            {{ formatCurrency(trx.gross_amount) }}
                        </td>
                    </tr>
                    <tr v-if="transactions.length === 0">
                        <td colspan="7" class="border border-gray-200 px-3 py-6 text-center text-gray-500">
                            Tidak ada data transaksi pada filter ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>