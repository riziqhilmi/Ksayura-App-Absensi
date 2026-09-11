<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import FormField from '../../../Components/FormField.vue';

const props = defineProps({
    mode: { type: String, required: true },
    employee: { type: Object, default: null },
    links: { type: Object, required: true },
});

const isEdit = computed(() => props.mode === 'edit');

const form = useForm({
    name: props.employee?.user?.name ?? '',
    email: props.employee?.user?.email ?? '',
    password: '',
    password_confirmation: '',
    phone: props.employee?.user?.phone ?? '',
    address: props.employee?.user?.address ?? '',
    position: props.employee?.position ?? '',
    daily_rate: props.employee?.daily_rate ?? '',
    hourly_rate: props.employee?.hourly_rate ?? '',
    hire_date: props.employee?.user?.hire_date ?? '',
    status: props.employee?.status ?? 'active',
});

const submit = () => {
    if (isEdit.value) {
        form.put(props.links.submit, { preserveScroll: true });
        return;
    }

    form.post(props.links.submit, { preserveScroll: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Karyawan' : 'Tambah Karyawan'" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Manajemen Karyawan</p>
                <h1 class="text-2xl font-bold text-slate-950">{{ isEdit ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola informasi akun login, data pribadi, dan pengaturan gaji karyawan.</p>
            </div>

            <Card>
                <form class="space-y-8" @submit.prevent="submit">
                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Informasi Akun Login</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField label="Nama Lengkap *" :error="form.errors.name">
                                <input v-model="form.name" type="text" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Email *" :error="form.errors.email">
                                <input v-model="form.email" type="email" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField :label="isEdit ? 'Password Baru' : 'Password *'" :error="form.errors.password">
                                <input v-model="form.password" type="password" :required="!isEdit" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <span class="mt-1 block text-xs text-slate-500">{{ isEdit ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 8 karakter' }}</span>
                            </FormField>
                            <FormField :label="isEdit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password *'">
                                <input v-model="form.password_confirmation" type="password" :required="!isEdit" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Nomor Telepon" :error="form.errors.phone">
                                <input v-model="form.phone" type="text" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField :label="isEdit ? 'Tanggal Bergabung' : 'Tanggal Bergabung *'" :error="form.errors.hire_date">
                                <input v-model="form.hire_date" type="date" :required="!isEdit" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Alamat" :error="form.errors.address" class="md:col-span-2">
                                <textarea v-model="form.address" rows="2" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                            </FormField>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Informasi Pekerjaan</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField label="Posisi">
                                <input v-model="form.position" type="text" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Gaji Harian *" :error="form.errors.daily_rate">
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-slate-500">Rp</span>
                                    <input v-model="form.daily_rate" type="number" required min="0" class="w-full rounded-lg border-slate-200 py-2.5 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </div>
                            </FormField>
                            <FormField label="Tarif Per Jam">
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-slate-500">Rp</span>
                                    <input v-model="form.hourly_rate" type="number" min="0" class="w-full rounded-lg border-slate-200 py-2.5 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                </div>
                            </FormField>
                            <FormField v-if="isEdit" label="Status *" :error="form.errors.status">
                                <select v-model="form.status" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                    <option value="resigned">Resign</option>
                                </select>
                            </FormField>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <Link :href="links.index" class="inline-flex justify-center rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </Link>
                        <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Update Data' : 'Simpan Karyawan') }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
