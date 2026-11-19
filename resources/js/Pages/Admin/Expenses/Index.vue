<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    expenses: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const canCreate = computed(() => ['super_admin', 'bendahara'].includes(page.props.auth?.user?.role));
const isSuperAdmin = computed(() => page.props.auth?.user?.role === 'super_admin');
const flashSuccess = computed(() => page.props.flash?.success || null);

const receiptInput = ref(null);

const todayJakarta = () =>
    new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date());

const form = useForm({
    infaq_category_id: props.categories[0]?.id || '',
    expense_date: todayJakarta(),
    amount: '',
    title: '',
    description: '',
    receipt: null,
});

const handleFileChange = (event) => {
    form.receipt = event.target.files?.[0] || null;
};

const submit = () => {
    form.post('/admin/expenses', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount', 'title', 'description', 'receipt');
            if (receiptInput.value) {
                receiptInput.value.value = '';
            }
        },
    });
};

const removeExpense = (id) => {
    if (confirm('Hapus catatan pengeluaran ini? Tindakan akan dicatat di Audit Log.')) {
        router.delete(`/admin/expenses/${id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).formatToParts(new Date(val));
    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}`;
};

const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Pengeluaran & Bukti Realisasi" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">Manajemen Pengeluaran &amp; Bukti Realisasi</h2>
        </template>

        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
            >
                {{ flashSuccess }}
            </div>

            <!-- Form Catat Pengeluaran -->
            <div v-if="canCreate" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 mb-4">Catat Pengeluaran Baru</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Pos Anggaran</label>
                        <select v-model="form.infaq_category_id" required class="w-full rounded-lg border-slate-300 text-sm">
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.infaq_category_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.infaq_category_id }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Pengeluaran</label>
                        <input
                            v-model="form.expense_date"
                            type="date"
                            required
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.expense_date" class="text-xs text-red-600 mt-1">
                            {{ form.errors.expense_date }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal (Rp)</label>
                        <input
                            v-model="form.amount"
                            type="number"
                            required
                            min="1"
                            placeholder="500000"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.amount" class="text-xs text-red-600 mt-1">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Keperluan</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Misal: Pembelian 50 Sak Semen"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.title" class="text-xs text-red-600 mt-1">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan Detail</label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="Nama toko / nomor nota"
                            class="w-full rounded-lg border-slate-300 text-sm"
                        />
                        <p v-if="form.errors.description" class="text-xs text-red-600 mt-1">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                            Bukti Kuitansi (JPG/PNG/PDF, max 4MB)
                        </label>
                        <input
                            ref="receiptInput"
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            @change="handleFileChange"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                        />
                        <p v-if="form.errors.receipt" class="text-xs text-red-600 mt-1">
                            {{ form.errors.receipt }}
                        </p>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold hover:bg-emerald-800 disabled:opacity-60"
                        >
                            Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>

            <!-- Riwayat Pengeluaran -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-5 py-3 text-left">Tanggal</th>
                                <th class="px-5 py-3 text-left">Kategori</th>
                                <th class="px-5 py-3 text-left">Keperluan</th>
                                <th class="px-5 py-3 text-left">Bukti</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-slate-50/50">
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                                    {{ formatDate(exp.expense_date) }}
                                </td>
                                <td class="px-5 py-3 text-slate-700">
                                    {{ exp.category?.name || '-' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-bold text-slate-900 block">{{ exp.title }}</span>
                                    <span class="text-xs text-slate-500">
                                        {{ exp.description || '-' }} &bull; Dicatat: {{ exp.creator?.name || '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <a
                                        v-if="exp.receipt_path"
                                        :href="`/storage/${exp.receipt_path}`"
                                        target="_blank"
                                        class="text-xs font-semibold text-emerald-700 hover:underline"
                                    >
                                        Lihat Bukti
                                    </a>
                                    <span v-else class="text-xs text-slate-400">Tidak ada</span>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-amber-700 whitespace-nowrap">
                                    {{ formatRupiah(exp.amount) }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <button
                                        v-if="isSuperAdmin"
                                        type="button"
                                        @click="removeExpense(exp.id)"
                                        class="text-xs font-semibold text-red-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                    <span v-else class="text-xs text-slate-400">Hapus: Super Admin</span>
                                </td>
                            </tr>
                            <tr v-if="!expenses.data.length">
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                    Belum ada catatan pengeluaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="expenses.links && expenses.links.length > 3" class="p-4 border-t border-slate-100 flex flex-wrap gap-1 justify-end">
                    <template v-for="(link, idx) in expenses.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            preserve-scroll
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition"
                            :class="link.active ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs text-slate-400 border border-slate-100"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>