<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Infaq Pembangunan Masjid Digital')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-bold text-lg">
                        M
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 block leading-tight">Masjid At Taqwa</span>
                        <span class="text-xs text-emerald-700 font-medium">Sistem Infaq Digital</span>
                    </div>
                </a>

                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'text-emerald-700 font-semibold' : 'text-slate-600 hover:text-emerald-700' }}">Beranda</a>
                    <a href="{{ route('public.profile') }}" class="{{ request()->routeIs('public.profile') ? 'text-emerald-700 font-semibold' : 'text-slate-600 hover:text-emerald-700' }}">Profil & Sejarah</a>
                    <a href="{{ route('public.development') }}" class="{{ request()->routeIs('public.development') ? 'text-emerald-700 font-semibold' : 'text-slate-600 hover:text-emerald-700' }}">Progress Pembangunan</a>
                    <a href="{{ route('public.transparency') }}" class="{{ request()->routeIs('public.transparency') ? 'text-emerald-700 font-semibold' : 'text-slate-600 hover:text-emerald-700' }}">Transparansi Dana</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-emerald-700 text-white text-sm font-medium hover:bg-emerald-800 transition">
                            Dashboard Takmir
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-100 transition">
                            Login Takmir
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="flex md:hidden gap-4 py-2.5 border-t border-slate-100 overflow-x-auto text-xs font-medium">
                <a href="{{ route('public.home') }}" class="whitespace-nowrap {{ request()->routeIs('public.home') ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">Beranda</a>
                <a href="{{ route('public.profile') }}" class="whitespace-nowrap {{ request()->routeIs('public.profile') ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">Profil & Sejarah</a>
                <a href="{{ route('public.development') }}" class="whitespace-nowrap {{ request()->routeIs('public.development') ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">Progress Pembangunan</a>
                <a href="{{ route('public.transparency') }}" class="whitespace-nowrap {{ request()->routeIs('public.transparency') ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">Transparansi Dana</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
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
                    <li><a href="{{ route('public.profile') }}" class="hover:text-white">Profil & Sejarah Masjid</a></li>
                    <li><a href="{{ route('public.development') }}" class="hover:text-white">Target & Progress Pembangunan</a></li>
                    <li><a href="{{ route('public.transparency') }}" class="hover:text-white">Laporan Pemasukan & Pengeluaran</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3">Kontak Pengurus & Bendahara</h3>
                <ul class="space-y-2 text-slate-400">
                    <li>Ketua Takmir: H. Ahmad Fauzi (0812-0000-1111)</li>
                    <li>Bendahara Pembangunan: Ust. Budi Santoso (0812-0000-2222)</li>
                    <li>Email: takmir@masjidalikhlas.or.id</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Takmir Masjid At Taqwa. Seluruh transaksi QRIS diverifikasi secara otomatis.
        </div>
    </footer>
</body>
</html>