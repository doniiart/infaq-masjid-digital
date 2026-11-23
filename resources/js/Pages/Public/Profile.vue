<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    tagline: {
        type: String,
        default: "Pusat ibadah, pendidikan Al-Qur'an, dan pemberdayaan ekonomi umat.",
    },
    history: {
        type: String,
        default: '',
    },
    committeeMembers: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Profil & Sejarah Masjid" />

    <PublicLayout>
        <div class="mx-auto max-w-5xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                    Profil Masjid At Taqwa
                </p>
                <h1 class="mt-1 text-3xl font-extrabold text-slate-900">
                    Profil &amp; Sejarah Ketakmiran
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ tagline }}
                </p>
            </div>

            <!-- Sejarah Singkat -->
            <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Sejarah Singkat</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-600">
                    {{ history }}
                </p>
            </div>

            <!-- Struktur Inti Panitia Pembangunan (Full Width setelah Visi & Misi dihapus) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Struktur Inti Panitia Pembangunan</h2>
                <dl class="mt-4 divide-y divide-slate-100 text-sm">
                    <div
                        v-for="(item, index) in committeeMembers"
                        :key="index"
                        class="flex flex-col justify-between gap-1 py-3 sm:flex-row sm:items-center"
                    >
                        <dt class="text-slate-500">{{ item.role }}</dt>
                        <dd class="font-semibold text-slate-900">{{ item.name }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </PublicLayout>
</template>