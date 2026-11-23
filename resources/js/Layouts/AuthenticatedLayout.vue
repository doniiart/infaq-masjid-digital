<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const currentUrl = computed(() => page.url);

const showingNavigationDropdown = ref(false);
const showingUserDropdown = ref(false);

const navItems = [
    { label: 'Dashboard', href: '/dashboard', exact: true },
    { label: 'Transaksi', href: '/admin/transactions' },
    { label: 'Kategori', href: '/admin/categories' },
    { label: 'Pengeluaran', href: '/admin/expenses' },
    { label: 'Laporan', href: '/admin/reports' },
    { label: 'Rekonsiliasi', href: '/admin/reconciliations' },
    { label: 'Audit Log', href: '/admin/audit-logs' },
    { label: 'Konten Publik', href: '/admin/public-content' },
];

const isActive = (item) => {
    if (item.exact) return currentUrl.value === item.href;
    return currentUrl.value.startsWith(item.href);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link href="/dashboard" class="flex items-center gap-2 font-bold text-emerald-800">
                                <span class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center text-sm">
                                    M
                                </span>
                                <span class="hidden sm:inline">Takmir Admin</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-5 sm:-my-px sm:ms-8 sm:flex text-sm">
                            <Link
                                v-for="item in navItems"
                                :key="item.href"
                                :href="item.href"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="
                                    isActive(item)
                                        ? 'border-emerald-600 text-slate-900 font-semibold'
                                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                                "
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <!-- Right Actions & User Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                        <a
                            href="/"
                            target="_blank"
                            class="text-xs font-semibold text-emerald-700 hover:underline"
                        >
                            Portal Publik &nearr;
                        </a>

                        <div class="relative">
                            <button
                                type="button"
                                @click="showingUserDropdown = !showingUserDropdown"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-600 bg-white hover:text-slate-900 focus:outline-none transition"
                            >
                                <div>
                                    {{ user.name }}
                                    <span v-if="user.role" class="text-xs text-emerald-700 font-semibold">
                                        ({{ user.role }})
                                    </span>
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </button>

                            <div
                                v-if="showingUserDropdown"
                                @click="showingUserDropdown = false"
                                class="fixed inset-0 z-40"
                            ></div>

                            <div
                                v-if="showingUserDropdown"
                                class="absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1"
                            >
                                <Link
                                    href="/profile"
                                    @click="showingUserDropdown = false"
                                    class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                >
                                    Profil Akun
                                </Link>
                                <button
                                    type="button"
                                    @click="logout"
                                    class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                >
                                    Keluar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            type="button"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    v-if="!showingNavigationDropdown"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div v-if="showingNavigationDropdown" class="sm:hidden border-t border-slate-200">
                <div class="pt-2 pb-3 space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="showingNavigationDropdown = false"
                        class="block ps-3 pe-4 py-2 border-l-4 text-base font-medium transition"
                        :class="
                            isActive(item)
                                ? 'border-emerald-600 text-emerald-700 bg-emerald-50 font-semibold'
                                : 'border-transparent text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300'
                        "
                    >
                        {{ item.label }}
                    </Link>
                </div>

                <div class="pt-4 pb-3 border-t border-slate-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-slate-800">
                            {{ user.name }}
                            <span v-if="user.role" class="text-xs text-emerald-700 font-semibold">
                                ({{ user.role }})
                            </span>
                        </div>
                        <div class="font-medium text-sm text-slate-500">{{ user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <a
                            href="/"
                            target="_blank"
                            class="block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-emerald-700 hover:bg-slate-50"
                        >
                            Portal Publik &nearr;
                        </a>
                        <Link
                            href="/profile"
                            @click="showingNavigationDropdown = false"
                            class="block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 hover:bg-slate-50"
                        >
                            Profil Akun
                        </Link>
                        <button
                            type="button"
                            @click="logout"
                            class="block w-full text-left ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 hover:bg-slate-50"
                        >
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>