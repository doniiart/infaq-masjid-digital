<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    mediaList: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('media'); // 'media' | 'profile'

const profileForm = useForm({
    tagline: props.profile.tagline || '',
    history: props.profile.history || '',
    committee_members: JSON.parse(JSON.stringify(props.profile.committee_members || [])),
});

const addMember = () => {
    profileForm.committee_members.push({ role: '', name: '' });
};

const removeMember = (index) => {
    if (profileForm.committee_members.length > 1) {
        profileForm.committee_members.splice(index, 1);
    }
};

const submitProfile = () => {
    profileForm.put('/admin/public-content/profile', {
        preserveScroll: true,
    });
};

const mediaForm = useForm({
    title: '',
    description: '',
    media_type: 'image',
    file: null,
    external_url: '',
});

const fileInputRef = ref(null);

const handleFileChange = (event) => {
    mediaForm.file = event.target.files[0] || null;
};

const submitMedia = () => {
    mediaForm.post('/admin/public-content/media', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            mediaForm.reset();
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
    });
};

const deleteMedia = (item) => {
    if (!confirm(`Hapus dokumentasi "${item.title}"?`)) return;
    router.delete(`/admin/public-content/media/${item.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kelola Konten Publik" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kelola Konten Portal Publik</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Perbarui dokumentasi foto &amp; video progress pembangunan serta informasi profil masjid yang tampil untuk jamaah.
                </p>
            </div>

            <!-- Flash Success -->
            <div
                v-if="$page.props.flash?.success"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Tab Switcher -->
            <div class="flex gap-2 border-b border-slate-200">
                <button
                    type="button"
                    @click="activeTab = 'media'"
                    :class="[
                        activeTab === 'media'
                            ? 'border-emerald-700 text-emerald-800 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-800',
                        'border-b-2 px-4 py-2.5 text-sm transition'
                    ]"
                >
                    Foto &amp; Video Progress Pembangunan ({{ mediaList.length }})
                </button>
                <button
                    type="button"
                    @click="activeTab = 'profile'"
                    :class="[
                        activeTab === 'profile'
                            ? 'border-emerald-700 text-emerald-800 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-800',
                        'border-b-2 px-4 py-2.5 text-sm transition'
                    ]"
                >
                    Profil, Sejarah &amp; Struktur Panitia
                </button>
            </div>

            <!-- TAB 1: GALERI PROGRESS PEMBANGUNAN -->
            <div v-if="activeTab === 'media'" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Form Upload -->
                <div class="lg:col-span-5">
                    <form
                        @submit.prevent="submitMedia"
                        class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2 class="text-base font-bold text-slate-900">Tambah Dokumentasi Baru</h2>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Jenis Media
                            </label>
                            <select
                                v-model="mediaForm.media_type"
                                class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="image">Foto (JPG, PNG, WebP)</option>
                                <option value="video">Upload Video (MP4, WebM, MOV)</option>
                                <option value="youtube">Link Video YouTube</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Judul Dokumentasi
                            </label>
                            <input
                                v-model="mediaForm.title"
                                type="text"
                                placeholder="Contoh: Pengecoran Tiang Lantai 2 Tahap I"
                                class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            />
                            <p v-if="mediaForm.errors.title" class="mt-1 text-xs text-rose-600">
                                {{ mediaForm.errors.title }}
                            </p>
                        </div>

                        <div v-if="mediaForm.media_type === 'image' || mediaForm.media_type === 'video'">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Pilih File {{ mediaForm.media_type === 'video' ? 'Video (Maks. 50 MB)' : 'Foto' }}
                            </label>
                            <input
                                ref="fileInputRef"
                                type="file"
                                :accept="mediaForm.media_type === 'video' ? 'video/mp4,video/webm,video/quicktime' : 'image/jpeg,image/png,image/webp'"
                                @change="handleFileChange"
                                class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-800 hover:file:bg-emerald-100"
                            />
                            <p v-if="mediaForm.errors.file" class="mt-1 text-xs text-rose-600">
                                {{ mediaForm.errors.file }}
                            </p>
                        </div>

                        <div v-if="mediaForm.media_type === 'youtube'">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                URL Video YouTube
                            </label>
                            <input
                                v-model="mediaForm.external_url"
                                type="url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            />
                            <p v-if="mediaForm.errors.external_url" class="mt-1 text-xs text-rose-600">
                                {{ mediaForm.errors.external_url }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Keterangan / Progres (Opsional)
                            </label>
                            <textarea
                                v-model="mediaForm.description"
                                rows="3"
                                placeholder="Catatan singkat perkembangan pembangunan..."
                                class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="mediaForm.processing"
                            class="w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-50"
                        >
                            {{ mediaForm.processing ? 'Mengunggah...' : 'Simpan & Publikasikan' }}
                        </button>
                    </form>
                </div>

                <!-- Daftar Dokumentasi -->
                <div class="space-y-4 lg:col-span-7">
                    <div
                        v-if="mediaList.length === 0"
                        class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500"
                    >
                        Belum ada foto atau video progress pembangunan yang diunggah.
                    </div>

                    <div
                        v-for="item in mediaList"
                        :key="item.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
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

                        <div class="flex items-start justify-between gap-4 p-4">
                            <div>
                                <span class="inline-block rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold uppercase text-emerald-800">
                                    {{ item.media_type === 'image' ? 'Foto' : item.media_type === 'video' ? 'Video MP4' : 'YouTube' }}
                                </span>
                                <h3 class="mt-1 font-bold text-slate-900">{{ item.title }}</h3>
                                <p v-if="item.description" class="mt-1 text-xs text-slate-600">
                                    {{ item.description }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="deleteMedia(item)"
                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PROFIL & STRUKTUR PANITIA -->
            <form
                v-if="activeTab === 'profile'"
                @submit.prevent="submitProfile"
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Subjudul / Tagline Profil Masjid
                    </label>
                    <input
                        v-model="profileForm.tagline"
                        type="text"
                        class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                    />
                    <p v-if="profileForm.errors.tagline" class="mt-1 text-xs text-rose-600">
                        {{ profileForm.errors.tagline }}
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Sejarah Singkat Masjid
                    </label>
                    <textarea
                        v-model="profileForm.history"
                        rows="5"
                        class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                    ></textarea>
                    <p v-if="profileForm.errors.history" class="mt-1 text-xs text-rose-600">
                        {{ profileForm.errors.history }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Struktur Inti Panitia Pembangunan
                        </label>
                        <button
                            type="button"
                            @click="addMember"
                            class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100"
                        >
                            + Tambah Jabatan
                        </button>
                    </div>

                    <div
                        v-for="(member, idx) in profileForm.committee_members"
                        :key="idx"
                        class="grid grid-cols-1 gap-3 sm:grid-cols-12"
                    >
                        <div class="sm:col-span-5">
                            <input
                                v-model="member.role"
                                type="text"
                                placeholder="Jabatan (misal: Ketua Takmir)"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <input
                                v-model="member.name"
                                type="text"
                                placeholder="Nama Pejabat / Tokoh"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                            />
                        </div>
                        <div class="flex items-center sm:col-span-1">
                            <button
                                type="button"
                                @click="removeMember(idx)"
                                class="w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t pt-4">
                    <button
                        type="submit"
                        :disabled="profileForm.processing"
                        class="rounded-xl bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-50"
                    >
                        {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>