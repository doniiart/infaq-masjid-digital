<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaction: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const canManage = computed(() => ['super_admin', 'bendahara'].includes(page.props.auth?.user?.role));
const flashSuccess = computed(() => page.props.flash?.success || null);

const reviewForm = useForm({
    review_status: props.transaction.review_status || 'NOT_REVIEWED',
});

const firstAllocation = computed(() => (props.transaction.allocations || [])[0] || null);

const allocForm = useForm({
    infaq_category_id: firstAllocation.value?.infaq_category_id || props.categories[0]?.id || '',
    notes: firstAllocation.value?.notes || '',
});

const submitReview = () => {
    reviewForm.patch(`/admin/transactions/${props.transaction.id}/review`, {
        preserveScroll: true,
    });
};

const submitAllocation = () => {
    allocForm.post(`/admin/transactions/${props.transaction.id}/allocate`, {
        preserveScroll: true,
    });
};

const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

const formatDateTimeWib = (val) => {
    if (!val) return '-';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).formatToParts(new Date(val));

    const get = (type) => parts.find((p) => p.type === type)?.value || '';
    return `${get('day')} ${get('month')} ${get('year')}, ${get('hour')}:${get('minute')}:${get('second')}`;
};

const formattedPayload = computed(() => {
    const raw = props.transaction.raw_payload;
    if (!raw) return 'null';
    if (typeof raw === 'string') {
        try {
            return JSON.stringify(JSON.parse(raw), null, 2);
        } catch {
            return raw;
        }
    }
    return JSON.stringify(raw, null, 2);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Transaksi: ${transaction.provider_transaction_id}`" />

        <template #header>
            <h2 class="font-bold text-xl text-slate-800">
                Detail Transaksi: {{ transaction.provider_transaction_id }}
            </h2>
            <Link href="/admin/transactions" class="text-sm text-slate-600 hover:underline">
                &larr; Kembali
            </Link>
        </template>

        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
            >
                {{ flashSuccess }}
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informasi Teknis Transaksi -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4 text-sm">
                    <h3 class="font-bold text-slate-900 text-base border-b pb-2">
                        Metadata Transaksi &amp; 3 Dimensi Status
                    </h3>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <dt class="text-xs text-slate-500">Provider Transaction ID</dt>
                            <dd class="font-mono font-bold">{{ transaction.provider_transaction_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Provider Order ID</dt>
                            <dd class="font-mono">{{ transaction.provider_order_id || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Merchant ID</dt>
                            <dd class="font-mono">{{ transaction.merchant_id || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Nominal</dt>
                            <dd class="font-extrabold text-emerald-700 text-lg">
                                {{ formatRupiah(transaction.gross_amount) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Metode &amp; Acquirer</dt>
                            <dd>{{ transaction.payment_type || '-' }} / {{ transaction.acquirer || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Issuer &amp; Cross Ref ID</dt>
                            <dd>{{ transaction.issuer || '-' }} / {{ transaction.merchant_cross_reference_id || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Waktu Transaksi</dt>
                            <dd>
                                {{
                                    transaction.transaction_time
                                        ? `${formatDateTimeWib(transaction.transaction_time)} WIB`
                                        : '-'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Waktu Settlement</dt>
                            <dd>
                                {{
                                    transaction.settlement_time
                                        ? formatDateTimeWib(transaction.settlement_time)
                                        : '-'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Status Pembayaran</dt>
                            <dd class="font-bold">{{ transaction.transaction_status }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Status Rekonsiliasi</dt>
                            <dd class="font-bold">{{ transaction.reconciliation_status }}</dd>
                        </div>
                    </dl>

                    <div class="pt-4">
                        <h4 class="text-xs font-bold uppercase text-slate-500 mb-2">Raw Payload Audit (JSONB)</h4>
                        <pre class="bg-slate-900 text-emerald-300 p-4 rounded-lg text-xs overflow-x-auto max-h-64">{{ formattedPayload }}</pre>
                    </div>
                </div>

                <!-- Panel Aksi Takmir (Pemeriksaan & Alokasi) -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="font-bold text-slate-900 mb-3">Pemeriksaan Administrasi Takmir</h3>
                        <form @submit.prevent="submitReview" class="space-y-3">
                            <select
                                v-model="reviewForm.review_status"
                                :disabled="!canManage"
                                class="w-full rounded-lg border-slate-300 text-sm disabled:bg-slate-50"
                            >
                                <option
                                    v-for="rv in ['NOT_REVIEWED', 'VERIFIED', 'FLAGGED']"
                                    :key="rv"
                                    :value="rv"
                                >
                                    {{ rv }}
                                </option>
                            </select>
                            <button
                                v-if="canManage"
                                type="submit"
                                :disabled="reviewForm.processing"
                                class="w-full py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900 disabled:opacity-60"
                            >
                                Simpan Status Pemeriksaan
                            </button>
                        </form>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="font-bold text-slate-900 mb-3">Alokasi Pos Kategori Dana</h3>
                        <form @submit.prevent="submitAllocation" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Kategori Pos</label>
                                <select
                                    v-model="allocForm.infaq_category_id"
                                    :disabled="!canManage"
                                    class="w-full rounded-lg border-slate-300 text-sm disabled:bg-slate-50"
                                >
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <p v-if="allocForm.errors.infaq_category_id" class="text-xs text-red-600 mt-1">
                                    {{ allocForm.errors.infaq_category_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Alokasi (Opsional)</label>
                                <textarea
                                    v-model="allocForm.notes"
                                    :disabled="!canManage"
                                    rows="2"
                                    class="w-full rounded-lg border-slate-300 text-sm disabled:bg-slate-50"
                                ></textarea>
                                <p v-if="allocForm.errors.notes" class="text-xs text-red-600 mt-1">
                                    {{ allocForm.errors.notes }}
                                </p>
                            </div>
                            <button
                                v-if="canManage"
                                type="submit"
                                :disabled="allocForm.processing"
                                class="w-full py-2 bg-emerald-700 text-white rounded-lg text-sm font-semibold hover:bg-emerald-800 disabled:opacity-60"
                            >
                                Simpan Alokasi Dana
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>