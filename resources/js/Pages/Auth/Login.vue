<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login Takmir" />

        <h1 class="text-xl font-bold text-slate-900 mb-1">Masuk ke Dashboard Takmir</h1>
        <p class="text-sm text-slate-500 mb-6">Gunakan akun resmi pengurus Masjid At Taqwa.</p>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                />
                <p v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                />
                <p v-if="form.errors.password" class="text-xs text-rose-600 mt-1">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    />
                    <span>Ingat saya</span>
                </label>
                <Link href="/" class="text-xs font-medium text-emerald-700 hover:underline">
                    &larr; Kembali ke Portal Publik
                </Link>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold text-sm shadow-sm transition"
            >
                {{ form.processing ? 'Memproses...' : 'Masuk' }}
            </button>
        </form>
    </GuestLayout>
</template>