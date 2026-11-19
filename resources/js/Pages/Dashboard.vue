<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    totalIncome: { type: [Number, String], default: 0 },
    settledCount: { type: Number, default: 0 },
    todayCount: { type: Number, default: 0 },
    monthCount: { type: Number, default: 0 },
    totalExpense: { type: [Number, String], default: 0 },
    developmentFund: { type: [Number, String], default: null },
    developmentIncome: { type: [Number, String], default: 0 },
    balance: { type: [Number, String], default: null },
    netBalance: { type: [Number, String], default: null },
    dailyChart: { type: Array, default: () => [] },
    monthlyChart: { type: Array, default: () => [] },
    recentTransactions: { type: Array, default: () => [] },
    recentActivities: { type: Array, default: () => [] },
});

const displayDevelopmentFund = computed(() => {
    if (props.developmentFund !== null) return Number(props.developmentFund);
    return Number(props.developmentIncome || 0);
});

const displayBalance = computed(() => {
    if (props.balance !== null) return Number(props.balance);
    if (props.netBalance !== null) return Number(props.netBalance);
    return Number(props.totalIncome || 0) - Number(props.totalExpense || 0);
});

const maxDailyAmount = computed(() => {
    if (!props.dailyChart.length) return 1;
    return Math.max(1, ...props.dailyChart.map((b) => Number(b.amount || 0)));
});

const barHeight = (amount) => {
    const val = Number(amount || 0);
    return Math.max(6, Math.round((val / maxDailyAmount.value) * 100));
};

const formatShortK = (amount) => {
    const val = Number(amount || 0);
    if (val <= 0) return '0';
    return Math.round(val / 1000).toLocaleString('id-ID') + 'k';
};

const monthPercentages = (m) => {
    const inc = Number(m.income || 0);
    const exp = Number(m.expense || 0);
    const sumPair = Math.max(1, inc + exp);
    return {
        incPct: Math.round((inc / sumPair) * 100),
        expPct: Math.round((exp / sumPair) * 100),
    };
};

const formatRupiah = (val) => 'Rp ' + Math.round(Number(val || 0)).toLocaleString('id-ID');

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
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Ringkasan Keuangan & Operasional Takmir" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Ringkasan Keuangan &amp; Operasional Takmir
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Kartu KPI Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">Total Infaq Masuk (Settlement)</p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ formatRupiah(totalIncome) }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ Number(settledCount || 0).toLocaleString('id-ID') }} transaksi settlement total
                    </p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">Dana Pos Pembangunan</p>
                    <p class="text-2xl font-extrabold text-teal-700 mt-1">{{ formatRupiah(displayDevelopmentFund) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Teralokasi khusus pembangunan</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">Total Pengeluaran</p>
                    <p class="text-2xl font-extrabold text-amber-700 mt-1">{{ formatRupiah(totalExpense) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Seluruh pos anggaran</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">Saldo Kas Bersih</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ formatRupiah(displayBalance) }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Hari ini: <strong>{{ todayCount }}</strong> trx &bull; Bulan ini: <strong>{{ monthCount }}</strong> trx
                    </p>
                </div>
            </div>

            <!-- Grafik Transaksi Harian & Pemasukan Bulanan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4">Grafik Infaq 7 Hari Terakhir</h3>
                    <div class="grid grid-cols-7 gap-2 items-end h-44 pt-6 border-b border-slate-100">
                        <div
                            v-for="(bar, idx) in dailyChart"
                            :key="idx"
                            class="flex flex-col items-center justify-end h-full"
                        >
                            <span class="text-[10px] text-slate-500 mb-1">{{ formatShortK(bar.amount) }}</span>
                            <div
                                class="w-full max-w-[32px] bg-emerald-600 rounded-t"
                                :style="{ height: `${barHeight(bar.amount)}%` }"
                            ></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-2 text-center text-[11px] text-slate-600 mt-2">
                        <span v-for="(bar, idx) in dailyChart" :key="idx">{{ bar.label }}</span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4">Pemasukan vs Pengeluaran (6 Bulan Terakhir)</h3>
                    <div class="space-y-3">
                        <div v-for="(m, idx) in monthlyChart" :key="idx" class="text-xs">
                            <div class="flex justify-between font-medium text-slate-700 mb-1">
                                <span>{{ m.label }}</span>
                                <span>
                                    Masuk: <strong class="text-emerald-700">{{ formatRupiah(m.income) }}</strong> |
                                    Keluar: <strong class="text-amber-700">{{ formatRupiah(m.expense) }}</strong>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex">
                                <div
                                    class="bg-emerald-600 h-2"
                                    :style="{ width: `${monthPercentages(m).incPct}%` }"
                                ></div>
                                <div
                                    class="bg-amber-500 h-2"
                                    :style="{ width: `${monthPercentages(m).expPct}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaksi Terbaru & Aktivitas Audit Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800">Transaksi Masuk Terbaru</h3>
                        <Link
                            href="/admin/transactions"
                            class="text-xs font-semibold text-emerald-700 hover:underline"
                        >
                            Kelola Semua &rarr;
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100 text-sm">
                        <div
                            v-for="trx in recentTransactions"
                            :key="trx.id"
                            class="py-2.5 flex justify-between items-center"
                        >
                            <div>
                                <Link
                                    :href="`/admin/transactions/${trx.id}`"
                                    class="font-semibold text-slate-800 hover:text-emerald-700"
                                >
                                    {{ trx.provider_transaction_id }}
                                </Link>
                                <p class="text-xs text-slate-500">
                                    {{ formatDateTimeWib(trx.transaction_time) }} &bull; {{ trx.transaction_status }}
                                </p>
                            </div>
                            <span class="font-bold text-emerald-700">{{ formatRupiah(trx.gross_amount) }}</span>
                        </div>
                        <p v-if="!recentTransactions.length" class="py-6 text-center text-slate-500 text-sm">
                            Belum ada transaksi masuk.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800">Aktivitas Takmir Terbaru (Audit Log)</h3>
                        <Link
                            href="/admin/audit-logs"
                            class="text-xs font-semibold text-emerald-700 hover:underline"
                        >
                            Semua Log &rarr;
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100 text-sm">
                        <div
                            v-for="log in recentActivities"
                            :key="log.id"
                            class="py-2.5 flex justify-between items-start gap-2"
                        >
                            <div>
                                <span class="font-mono text-xs font-semibold text-slate-800">{{ log.action }}</span>
                                <p class="text-xs text-slate-500">
                                    Oleh: {{ log.user?.name || 'Sistem' }} ({{ log.ip_address || '-' }})
                                </p>
                            </div>
                            <span class="text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDateTimeWib(log.created_at) }}
                            </span>
                        </div>
                        <p v-if="!recentActivities.length" class="py-6 text-center text-slate-500 text-sm">
                            Belum ada log aktivitas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>