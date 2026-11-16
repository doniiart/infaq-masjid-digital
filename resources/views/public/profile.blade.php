@extends('layouts.public')

@section('title', 'Profil & Sejarah Masjid')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Profil Lembaga</span>
        <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Profil &amp; Sejarah Masjid At Taqwa</h1>
        <p class="text-slate-600 mt-2">Pusat ibadah, pendidikan Al-Qur'an, dan pemberdayaan ekonomi umat.</p>
    </div>

    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-xl font-bold text-slate-900">Sejarah Singkat</h2>
        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
            Masjid At Taqwa didirikan atas swadaya masyarakat dan wakaf tanah warga setempat sebagai sarana ibadah berjamaah serta pembinaan generasi muda. Seiring bertambahnya jumlah jamaah shalat Jumat dan kegiatan majelis taklim, Takmir mencanangkan program perluasan bangunan utama dua lantai serta modernisasi pencatatan infaq berbasis QRIS Statis agar setiap rupiah amanah jamaah dapat diaudit secara terbuka.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Visi &amp; Misi Ketakmiran</h2>
            <ul class="space-y-2 text-sm text-slate-600 list-disc list-inside">
                <li>Memakmurkan masjid dengan ibadah wajib, kajian rutin, dan pendidikan TPQ.</li>
                <li>Menyelesaikan pembangunan ruang ibadah lantai dua secara bertahap dan terukur.</li>
                <li>Menerapkan tata kelola keuangan syariah yang transparan, terekonsiliasi, dan mudah diakses jamaah.</li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Struktur Inti Panitia Pembangunan</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <dt class="text-slate-500">Pelindung</dt>
                    <dd class="font-semibold text-slate-800">Pemerintah Desa &amp; Tokoh Masyarakat</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <dt class="text-slate-500">Ketua Takmir</dt>
                    <dd class="font-semibold text-slate-800">H. Ahmad Fauzi</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <dt class="text-slate-500">Bendahara Pembangunan</dt>
                    <dd class="font-semibold text-slate-800">Ust. Budi Santoso</dd>
                </div>
                <div class="flex justify-between py-1">
                    <dt class="text-slate-500">Koordinator Teknis &amp; IT</dt>
                    <dd class="font-semibold text-slate-800">Tim Media &amp; Digitalisasi Masjid</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection