<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    summary: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    recentTransactions: { type: Array, default: () => [] },
    recentExpenses: { type: Array, default: () => [] },
    hasOfficialQris: { type: Boolean, default: false },
});

const dataSummary = computed(() => {
    const src = Object.keys(props.summary || {}).length ? props.summary : (props.stats || {});
    return {
        total_income: Number(src.total_income ?? 0),
        settled_count: Number(src.settled_count ?? 0),
        total_expense: Number(src.total_expense ?? 0),
        current_balance: Number(src.current_balance ?? src.net_balance ?? 0),
        progress_percent: Number(src.progress_percent ?? 0),
        development_collected: Number(src.development_collected ?? src.development_income ?? 0),
        development_target: Number(src.development_target ?? 0),
    };
});

const formatRupiah = (val) => 'Rp ' + Math.round(Number(val || 0)).toLocaleString('id-ID');

const formatDateTimeWib = (val) => {
    if (!val) return '-';
    const parsed = new Date(val);
    if (Number.isNaN(parsed.getTime())) return String(val);

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
    return `${get('day')} ${get('month')} ${get('year')}, ${get('hour')}:${get('minute')}`;
};

const formatDate = (val) => {
    if (!val) return '-';
    const parsed = new Date(val);
    if (Number.isNaN(parsed.getTime())) return String(val);

    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).formatToParts(parsed);

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}`;
};
</script>

<template>
    <PublicLayout>
        <Head title="Beranda - Sistem Infaq Pembangunan Masjid Digital" />

        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-900 text-white py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-7 space-y-5">
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-700/70 border border-emerald-500/40 text-xs font-semibold tracking-wide uppercase">
                        Transparansi Real-Time Berbasis QRIS Statis
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight">
                        Bersama Membangun Rumah Allah dengan Transparan &amp; Akuntabel
                    </h1>
                    <p class="text-emerald-100 text-base leading-relaxed">
                        Jamaah dapat menyalurkan infaq pembangunan kapan saja dengan memindai QRIS Statis masjid dari aplikasi Mobile Banking atau E-Wallet tanpa perlu membuat akun maupun mengisi formulir.
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <Link
                            href="/transparansi"
                            class="px-5 py-2.5 rounded-lg bg-white text-emerald-900 font-semibold text-sm hover:bg-emerald-50 transition"
                        >
                            Lihat Laporan Keuangan
                        </Link>
                        <Link
                            href="/pembangunan"
                            class="px-5 py-2.5 rounded-lg border border-emerald-400/50 text-white font-semibold text-sm hover:bg-emerald-800 transition"
                        >
                            Detail Pembangunan
                        </Link>
                    </div>
                </div>

                <!-- Kartu Informasi QRIS Statis (Sarana Informasi Tambahan) -->
                <div class="lg:col-span-5">
                    <div class="bg-white text-slate-800 rounded-2xl p-6 shadow-xl border border-emerald-100">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                    QRIS Statis Resmi
                                </span>
                                <h2 class="font-bold text-slate-900">Infaq Pembangunan Masjid</h2>
                            </div>
                            <span class="px-2.5 py-1 rounded bg-slate-100 text-xs font-semibold text-slate-600">
                                IDR
                            </span>
                        </div>

                        <img
                            v-if="hasOfficialQris"
                            src="/images/qris-resmi-masjid.png"
                            alt="QRIS Resmi Masjid"
                            class="w-56 h-56 mx-auto object-contain rounded-lg border border-slate-200 p-2"
                        />
                        <div
                            v-else
                            class="w-56 h-56 mx-auto rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 flex flex-col items-center justify-center p-4 text-center"
                        >
                            <span class="text-xs font-semibold text-emerald-800">Slot QRIS Resmi Midtrans</span>
                            <span class="text-[11px] text-slate-500 mt-1">
                                Akan dipasang setelah aktivasi GoPay Static QRIS (Phase 6)
                            </span>
                        </div>

                        <ol class="mt-4 space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-xl">
                            <li>1. Buka aplikasi Mobile Banking atau E-Wallet Anda.</li>
                            <li>2. Pilih menu <strong>Scan QRIS</strong> dan pindai kode di atas atau di papan masjid.</li>
                            <li>3. Masukkan nominal infaq secara mandiri dan periksa nama merchant masjid.</li>
                            <li>4. Transaksi terverifikasi otomatis masuk ke laporan transparansi.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ringkasan Statistik Keuangan -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-7">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Total Infaq Masuk (Settlement)
                    </p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-2">
                        {{ formatRupiah(dataSummary.total_income) }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        Dari {{ dataSummary.settled_count.toLocaleString('id-ID') }} transaksi terverifikasi
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Total Pengeluaran Terealisasi
                    </p>
                    <p class="text-2xl font-extrabold text-amber-700 mt-2">
                        {{ formatRupiah(dataSummary.total_expense) }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Tercatat beserta bukti kuitansi</p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo Kas Tersedia</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">
                        {{ formatRupiah(dataSummary.current_balance) }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Selisih pemasukan settlement &amp; pengeluaran</p>
                </div>
            </div>
        </section>

        <!-- Progress Pembangunan -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Progress Dana Pembangunan Utama</h2>
                        <p class="text-sm text-slate-600">
                            Dana khusus yang telah dialokasikan ke pos Infaq Pembangunan.
                        </p>
                    </div>
                    <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-3.5 py-1.5 rounded-lg">
                        Tercapai {{ dataSummary.progress_percent }}%
                    </span>
                </div>

                <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                    <div
                        class="bg-emerald-600 h-4 rounded-full transition-all"
                        :style="{ width: `${Math.min(100, Math.max(0, dataSummary.progress_percent))}%` }"
                    ></div>
                </div>

                <div class="flex justify-between text-xs sm:text-sm font-medium text-slate-600 mt-3">
                    <span>
                        Terkumpul: <strong class="text-slate-900">{{ formatRupiah(dataSummary.development_collected) }}</strong>
                    </span>
                    <span>
                        Target Anggaran: <strong class="text-slate-900">{{ formatRupiah(dataSummary.development_target) }}</strong>
                    </span>
                </div>
            </div>
        </section>

        <!-- Transaksi & Pengeluaran Terbaru -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pemasukan Terbaru -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900">Infaq Masuk Terbaru</h3>
                    <Link href="/transparansi" class="text-xs font-semibold text-emerald-700 hover:underline">
                        Semua Transaksi &rarr;
                    </Link>
                </div>
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="trx in recentTransactions"
                        :key="trx.id"
                        class="py-3 flex items-center justify-between text-sm"
                    >
                        <div>
                            <p class="font-semibold text-slate-800">
                                Infaq via {{ String(trx.acquirer || trx.payment_type || 'QRIS').toUpperCase() }}
                            </p>
                            <p class="text-xs text-slate-500">{{ formatDateTimeWib(trx.transaction_time) }} WIB</p>
                        </div>
                        <span class="font-bold text-emerald-700">+ {{ formatRupiah(trx.gross_amount) }}</span>
                    </div>
                    <p v-if="!recentTransactions.length" class="py-6 text-center text-sm text-slate-500">
                        Belum ada transaksi settlement yang tercatat.
                    </p>
                </div>
            </div>

            <!-- Pengeluaran Terbaru -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900">Realisasi Pengeluaran Terbaru</h3>
                    <Link href="/transparansi" class="text-xs font-semibold text-emerald-700 hover:underline">
                        Semua Pengeluaran &rarr;
                    </Link>
                </div>
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="exp in recentExpenses"
                        :key="exp.id"
                        class="py-3 flex items-center justify-between text-sm"
                    >
                        <div>
                            <p class="font-semibold text-slate-800">{{ exp.title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ exp.category?.name || exp.category_name || 'Umum' }} &bull; {{ formatDate(exp.expense_date) }}
                            </p>
                        </div>
                        <span class="font-bold text-amber-700">- {{ formatRupiah(exp.amount) }}</span>
                    </div>
                    <p v-if="!recentExpenses.length" class="py-6 text-center text-sm text-slate-500">
                        Belum ada catatan pengeluaran yang dipublikasikan.
                    </p>
                </div>
            </div>
        </section>

        <!-- Dokumentasi Kegiatan -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
            <h2 class="text-xl font-bold text-slate-900 mb-5">Dokumentasi Kegiatan &amp; Pembangunan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded">Tahap 1</span>
                    <h3 class="font-bold text-slate-900 mt-3">Pekerjaan Pondasi &amp; Struktur Utama</h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Pengecoran sloof dan kolom utama perluasan ruang shalat lantai dasar.
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded">Tahap 2</span>
                    <h3 class="font-bold text-slate-900 mt-3">Pemasangan Rangka Atap &amp; Kubah</h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Persiapan pengadaan material baja ringan dan sistem sirkulasi udara ruang utama.
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded">
                        Program Umat
                    </span>
                    <h3 class="font-bold text-slate-900 mt-3">Kajian Rutin &amp; Audit Transparansi Bulanan</h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Penyampaian laporan pemasukan QRIS dan pengeluaran pembangunan setiap awal bulan.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>