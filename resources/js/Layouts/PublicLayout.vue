<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentUrl = computed(() => page.url);

const navItems = [
    { label: 'Beranda', href: '/' },
    { label: 'Profil & Sejarah', href: '/profil' },
    { label: 'Progress Pembangunan', href: '/pembangunan' },
    { label: 'Transparansi Dana', href: '/transparansi' },
];

const isActive = (href) => {
    if (href === '/') return currentUrl.value === '/';
    return currentUrl.value.startsWith(href);
};
</script>

<template>
    <div class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen">
        <!-- Navbar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-bold text-lg">
                            M
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 block leading-tight">Masjid At Taqwa</span>
                            <span class="text-xs text-emerald-700 font-medium">Sistem Infaq Digital</span>
                        </div>
                    </Link>

                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                        <Link
                            v-for="item in navItems"
                            :key="item.href"
                            :href="item.href"
                            :class="
                                isActive(item.href)
                                    ? 'text-emerald-700 font-semibold'
                                    : 'text-slate-600 hover:text-emerald-700'
                            "
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="flex items-center gap-3">
                        <Link
                            v-if="user"
                            href="/dashboard"
                            class="px-4 py-2 rounded-lg bg-emerald-700 text-white text-sm font-medium hover:bg-emerald-800 transition"
                        >
                            Dashboard Takmir
                        </Link>
                        <Link
                            v-else
                            href="/login"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-100 transition"
                        >
                            Login Takmir
                        </Link>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div class="flex md:hidden gap-4 py-2.5 border-t border-slate-100 overflow-x-auto text-xs font-medium">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="whitespace-nowrap"
                        :class="isActive(item.href) ? 'text-emerald-700 font-semibold' : 'text-slate-600'"
                    >
                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer & Kontak Pengurus -->
        <footer id="kontak" class="bg-slate-900 text-slate-300 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <div>
                    <h3 class="text-white font-bold text-base mb-3">Takmir Masjid At Taqwa</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Jl. Raya Wonosari - Klaten, Jawa Tengah. Sistem pencatatan infaq pembangunan terintegrasi QRIS Statis untuk akuntabilitas dan transparansi umat.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-3">Navigasi Transparansi</h3>
                    <ul class="space-y-2 text-slate-400">
                        <li>
                            <Link href="/profil" class="hover:text-white">Profil &amp; Sejarah Masjid</Link>
                        </li>
                        <li>
                            <Link href="/pembangunan" class="hover:text-white">Target &amp; Progress Pembangunan</Link>
                        </li>
                        <li>
                            <Link href="/transparansi" class="hover:text-white">Laporan Pemasukan &amp; Pengeluaran</Link>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-3">Kontak Pengurus &amp; Bendahara</h3>
                    <ul class="space-y-2 text-slate-400">
                        <li>Ketua Takmir: H. Ahmad Fauzi (0812-0000-1111)</li>
                        <li>Bendahara Pembangunan: Ust. Budi Santoso (0812-0000-2222)</li>
                        <li>Email: takmir@masjidalikhlas.or.id</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} Takmir Masjid At Taqwa. Seluruh transaksi QRIS diverifikasi secara otomatis.
            </div>
        </footer>
    </div>
</template>