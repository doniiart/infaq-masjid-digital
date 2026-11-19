<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const canManage = computed(() => ['super_admin', 'bendahara'].includes(page.props.auth?.user?.role));
const flashSuccess = computed(() => page.props.flash?.success || null);

const createForm = useForm({
    name: '',
    target_amount: '',
    description: '',
});

const storeCategory = () => {
    createForm.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const rows = ref([]);

watch(
    () => props.categories,
    (cats) => {
        rows.value = (cats || []).map((cat) => ({
            id: cat.id,
            name: cat.name || '',
            description: cat.description || '',
            target_amount: cat.target_amount ?? '',
            is_active: Boolean(cat.is_active),
            is_default: Boolean(cat.is_default),
            processing: false,
        }));
    },
    { immediate: true, deep: true }
);

const updateCategory = (row) => {
    row.processing = true;
    router.put(
        `/admin/categories/${row.id}`,
        {
            name: row.name,
            description: row.description,
            target_amount: row.target_amount === '' ? null : row.target_amount,
            is_active: row.is_default ? true : Boolean(row.is_active),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                row.processing = false;
            },
        }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Kategori & Pos Anggaran Infaq" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Manajemen Kategori &amp; Pos Anggaran Infaq</h2>
        </template>

        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
            >
                {{ flashSuccess }}
            </div>

            <!-- Tambah Kategori Baru -->
            <div v-if="canManage" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 mb-4">Tambah Kategori Pos Dana Baru</h3>
                <form @submit.prevent="storeCategory" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            required
                            placeholder="Misal: Infaq Pendidikan TPQ"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Target Anggaran (Opsional)</label>
                        <input
                            v-model="createForm.target_amount"
                            type="number"
                            placeholder="0"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
                        <input
                            v-model="createForm.description"
                            type="text"
                            placeholder="Deskripsi singkat peruntukan"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="w-full py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800 disabled:opacity-60"
                        >
                            Tambah Kategori
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Kategori -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-5 py-3 text-left">Nama &amp; Keterangan</th>
                                <th class="px-5 py-3 text-left">Target Dana</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th v-if="canManage" class="px-5 py-3 text-right">Simpan Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-50/50">
                                <td class="px-5 py-3 space-y-1">
                                    <input
                                        v-model="row.name"
                                        type="text"
                                        :disabled="!canManage"
                                        class="w-full rounded border-slate-300 text-sm font-semibold disabled:bg-slate-50"
                                    />
                                    <input
                                        v-model="row.description"
                                        type="text"
                                        :disabled="!canManage"
                                        class="w-full rounded border-slate-200 text-xs text-slate-600 disabled:bg-slate-50"
                                    />
                                    <span
                                        v-if="row.is_default"
                                        class="inline-block text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded"
                                    >
                                        Pos Default Transaksi QRIS Otomatis
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <input
                                        v-model="row.target_amount"
                                        type="number"
                                        :disabled="!canManage"
                                        class="w-40 rounded border-slate-300 text-sm disabled:bg-slate-50"
                                    />
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <label class="inline-flex items-center gap-1.5 text-xs">
                                        <input
                                            v-model="row.is_active"
                                            type="checkbox"
                                            :disabled="!canManage || row.is_default"
                                        />
                                        <span>Aktif</span>
                                    </label>
                                </td>
                                <td v-if="canManage" class="px-5 py-3 text-right">
                                    <button
                                        type="button"
                                        @click="updateCategory(row)"
                                        :disabled="row.processing"
                                        class="px-3.5 py-1.5 bg-slate-800 text-white rounded text-xs font-semibold hover:bg-slate-900 disabled:opacity-60"
                                    >
                                        Update
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!rows.length">
                                <td :colspan="canManage ? 4 : 3" class="px-5 py-8 text-center text-slate-500 text-xs">
                                    Belum ada kategori pos dana.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>