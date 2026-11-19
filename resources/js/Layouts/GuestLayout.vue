<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = computed(() => page.props.appName || 'Masjid At Taqwa');
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-slate-100 px-4">
        <Link href="/" class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center shadow">
                M
            </div>
            <div>
                <span class="font-bold text-lg text-slate-900 block leading-tight">{{ appName }}</span>
                <span class="text-xs text-slate-500">Panel Pengurus &amp; Takmir Masjid</span>
            </div>
        </Link>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-7">
            <slot />
        </div>
    </div>
</template>