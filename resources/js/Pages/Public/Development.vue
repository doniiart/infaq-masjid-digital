<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    summary: { type: Object, default: () => ({}) },
    developmentStats: { type: Object, default: () => ({}) },
    developmentExpenses: { type: Array, default: () => [] },
    mediaList: {type: Array,default: () => [] },
});

const dataSummary = computed(() => {
    const src = Object.keys(props.summary || {}).length ? props.summary : (props.developmentStats || {});
    const target = Number(src.development_target ?? 0);
    const collected = Number(src.development_collected ?? src.collected ?? 0);
    const remaining = Math.max(0, target - collected);
    const percent = Number(src.progress_percent ?? (target > 0 ? Math.min(100, Math.round((collected / target) * 100)) : 0));

    return {
        development_target: target,
        development_collected: collected,
        remaining,
        progress_percent: percent,
    };
});

const formatRupiah = (val) => 'Rp ' + Math.round(Number(val || 0)).toLocaleString('id-ID');

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
        <Head title="Informasi & Progress Pembangunan Masjid" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Rencana &amp; Realisasi</span>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Informasi &amp; Progress Pembangunan</h1>
                <p class="text-slate-600 mt-2">
                    Pantau target kebutuhan dana, persentase capaian, serta rincian penggunaan dana pembangunan.
                </p>
            </div>

            <!-- Kartu Capaian -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-semibold">Target Anggaran</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1">
                            {{ formatRupiah(dataSummary.development_target) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-semibold">Dana Pembangunan Terkumpul</p>
                        <p class="text-2xl font-extrabold text-emerald-700 mt-1">
                            {{ formatRupiah(dataSummary.development_collected) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-semibold">Kekurangan Dana</p>
                        <p class="text-2xl font-extrabold text-amber-700 mt-1">
                            {{ formatRupiah(dataSummary.remaining) }}
                        </p>
                    </div>
                </div>

                <div class="w-full bg-slate-100 rounded-full h-5 overflow-hidden">
                    <div
                        class="bg-emerald-600 h-5 rounded-full text-xs text-white font-bold flex items-center justify-center"
                        :style="{ width: `${Math.min(100, Math.max(6, dataSummary.progress_percent))}%` }"
                    >
                        {{ dataSummary.progress_percent }}%
                    </div>
                </div>
            </div>

    <!-- Dokumentasi Foto & Video Progress Pembangunan -->
            <div v-if="mediaList.length > 0" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Dokumentasi Progress Pembangunan</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Foto dan video perkembangan fisik pembangunan Masjid At Taqwa.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div
                        v-for="item in mediaList"
                        :key="item.id"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                    >
                        <div class="aspect-video w-full bg-slate-900">
                            <img
                                v-if="item.media_type === 'image'"
                                :src="item.media_url"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                            <video
                                v-else-if="item.media_type === 'video'"
                                :src="item.media_url"
                                controls
                                preload="metadata"
                                class="h-full w-full object-contain"
                            ></video>
                            <iframe
                                v-else-if="item.media_type === 'youtube' && item.youtube_embed_url"
                                :src="item.youtube_embed_url"
                                class="h-full w-full"
                                frameborder="0"
                                allowfullscreen
                            ></iframe>
                        </div>

                        <div class="p-4">
                            <h3 class="text-sm font-bold text-slate-900">{{ item.title }}</h3>
                            <p v-if="item.description" class="mt-1 text-xs leading-relaxed text-slate-600">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pengeluaran Khusus Pembangunan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Rincian Pengeluaran Pos Pembangunan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Tanggal</th>
                                <th class="px-6 py-3 text-left">Keperluan</th>
                                <th class="px-6 py-3 text-left">Keterangan</th>
                                <th class="px-6 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in developmentExpenses" :key="item.id">
                                <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ formatDate(item.expense_date) }}
                                </td>
                                <td class="px-6 py-3.5 font-semibold text-slate-900">
                                    {{ item.title }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-600">
                                    {{ item.description || '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-amber-700 whitespace-nowrap">
                                    {{ formatRupiah(item.amount) }}
                                </td>
                            </tr>
                            <tr v-if="!developmentExpenses.length">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    Belum ada pengeluaran pada kategori Infaq Pembangunan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>