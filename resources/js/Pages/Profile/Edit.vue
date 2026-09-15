<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import AppShell from '../../Components/AppShell.vue';
import Card from '../../Components/Card.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    user: { type: Object, required: true },
    links: { type: Object, required: true },
});

const page = usePage();

const status = computed(() => page.props.flash?.status);

const profileForm = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
    address: props.user.address ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

const submitProfile = () => {
    profileForm.patch(props.links.profile, {
        preserveScroll: true,
    });
};

const submitPassword = () => {
    passwordForm.put(props.links.password, {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const deleteAccount = () => {
    deleteForm.delete(props.links.destroy, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Profil" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Akun</p>
                <h1 class="text-2xl font-bold text-slate-950">Profil Saya</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola kontak, alamat, dan password akun.</p>
            </div>

            <div
                v-if="status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"
            >
                Perubahan berhasil disimpan.
            </div>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
                <Card>
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="flex h-28 w-28 items-center justify-center rounded-full bg-emerald-600 text-3xl font-black text-white ring-4 ring-emerald-50"
                        >
                            {{ user.avatar_initial || user.name?.charAt(0) || 'U' }}
                        </div>

                        <h2 class="mt-4 text-lg font-bold text-slate-950">{{ user.name }}</h2>
                        <p class="text-sm text-slate-500">{{ user.email }}</p>
                        <div class="mt-4 w-full rounded-lg bg-slate-50 p-3 text-left text-sm text-slate-600">
                            <p><b class="text-slate-800">No HP:</b> {{ user.phone || '-' }}</p>
                            <p class="mt-1"><b class="text-slate-800">Alamat:</b> {{ user.address || '-' }}</p>
                        </div>

                        <p class="mt-5 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">
                            Avatar memakai inisial nama secara otomatis.
                        </p>
                    </div>
                </Card>

                <div class="space-y-6">
                    <Card>
                        <form class="space-y-5" @submit.prevent="submitProfile">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Informasi Profil</h2>
                                <p class="mt-1 text-sm text-slate-500">Nama, email, nomor HP, dan alamat yang digunakan di data karyawan.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <FormField label="Nama Lengkap" :error="profileForm.errors.name">
                                    <input v-model="profileForm.name" type="text" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>

                                <FormField label="Email" :error="profileForm.errors.email">
                                    <input v-model="profileForm.email" type="email" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>

                                <FormField label="No HP" :error="profileForm.errors.phone">
                                    <input v-model="profileForm.phone" type="text" inputmode="tel" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>

                                <FormField label="Alamat" :error="profileForm.errors.address" class="md:col-span-2">
                                    <textarea v-model="profileForm.address" rows="3" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                                </FormField>
                            </div>

                            <div class="flex justify-end border-t border-slate-100 pt-5">
                                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="profileForm.processing">
                                    {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                                </button>
                            </div>
                        </form>
                    </Card>

                    <Card>
                        <form class="space-y-5" @submit.prevent="submitPassword">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Ubah Password</h2>
                                <p class="mt-1 text-sm text-slate-500">Gunakan password yang kuat dan tidak mudah ditebak.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <FormField label="Password Saat Ini" :error="passwordForm.errors.current_password">
                                    <input v-model="passwordForm.current_password" type="password" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>

                                <FormField label="Password Baru" :error="passwordForm.errors.password">
                                    <input v-model="passwordForm.password" type="password" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>

                                <FormField label="Konfirmasi Password">
                                    <input v-model="passwordForm.password_confirmation" type="password" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </FormField>
                            </div>

                            <div class="flex justify-end border-t border-slate-100 pt-5">
                                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-60" :disabled="passwordForm.processing">
                                    {{ passwordForm.processing ? 'Menyimpan...' : 'Update Password' }}
                                </button>
                            </div>
                        </form>
                    </Card>

                    <Card>
                        <form class="space-y-5" @submit.prevent="deleteAccount">
                            <div>
                                <h2 class="text-base font-bold text-red-700">Hapus Akun</h2>
                                <p class="mt-1 text-sm text-slate-500">Tindakan ini akan menghapus akun secara permanen.</p>
                            </div>

                            <FormField label="Konfirmasi Password" :error="deleteForm.errors.password">
                                <input v-model="deleteForm.password" type="password" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:ring-red-100">
                            </FormField>

                            <div class="flex justify-end border-t border-slate-100 pt-5">
                                <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60" :disabled="deleteForm.processing">
                                    {{ deleteForm.processing ? 'Menghapus...' : 'Hapus Akun' }}
                                </button>
                            </div>
                        </form>
                    </Card>
                </div>
            </section>
        </div>
    </AppShell>
</template>
