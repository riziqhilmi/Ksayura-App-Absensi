<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

defineProps({
    employee: { type: Object, required: true },
    links: { type: Object, required: true },
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const statusLabel = (value) => ({
    active: 'Aktif',
    inactive: 'Tidak Aktif',
    resigned: 'Resign',
}[value] || value);
</script>

<template>
    <Head title="Detail Karyawan" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <section class="rounded-lg bg-emerald-700 p-6 text-white shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/15 text-3xl font-bold ring-1 ring-white/20">
                            {{ employee.user?.name?.charAt(0) || 'K' }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ employee.user?.name }}</h1>
                            <p class="mt-1 text-sm text-emerald-100">{{ employee.position || 'Staff' }}</p>
                        </div>
                    </div>
                    <span class="w-fit rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-white/20">
                        {{ statusLabel(employee.status) }}
                    </span>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="text-lg font-bold text-slate-900">Informasi Pribadi</h2>
                    <div class="mt-4 divide-y divide-slate-100 text-sm">
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Nama Lengkap</span><b class="text-right">{{ employee.user?.name }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Email</span><b class="text-right">{{ employee.user?.email }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Telepon</span><b class="text-right">{{ employee.user?.phone || '-' }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Alamat</span><b class="text-right">{{ employee.user?.address || '-' }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Tanggal Bergabung</span><b class="text-right">{{ employee.user?.hire_date_label }}</b></p>
                    </div>
                </Card>

                <Card>
                    <h2 class="text-lg font-bold text-slate-900">Informasi Pekerjaan</h2>
                    <div class="mt-4 divide-y divide-slate-100 text-sm">
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Kode Karyawan</span><b class="font-mono">{{ employee.employee_code }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Posisi</span><b>{{ employee.position || '-' }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Gaji Harian</span><b class="text-emerald-600">{{ formatCurrency(employee.daily_rate) }}</b></p>
                        <p v-if="employee.hourly_rate" class="flex justify-between gap-4 py-3"><span class="text-slate-500">Tarif Per Jam</span><b>{{ formatCurrency(employee.hourly_rate) }}</b></p>
                        <p class="flex justify-between gap-4 py-3"><span class="text-slate-500">Status</span><b>{{ statusLabel(employee.status) }}</b></p>
                    </div>
                </Card>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Link :href="links.index" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali
                </Link>
                <Link :href="links.edit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Edit Karyawan
                </Link>
            </div>
        </div>
    </AppShell>
</template>
