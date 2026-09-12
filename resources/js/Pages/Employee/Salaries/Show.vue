<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

defineProps({
    salary: { type: Object, required: true },
    links: { type: Object, required: true },
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const statusLabels = { draft: 'Draft', calculated: 'Dihitung', paid: 'Dibayar' };
const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-700 ring-slate-200',
    calculated: 'bg-amber-50 text-amber-700 ring-amber-200',
    paid: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');
</script>

<template>
    <Head title="Detail Gaji Saya" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <section class="rounded-lg bg-emerald-700 p-6 text-white shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-emerald-100">Periode Gaji</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ salary.period }}</h1>
                        <p class="mt-1 text-sm text-emerald-100">{{ salary.start_date_label }} - {{ salary.end_date_label }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-sm text-emerald-100">Total Gaji</p>
                        <p class="text-3xl font-bold">{{ formatCurrency(salary.total_salary) }}</p>
                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="statusClass(salary.status)">
                            {{ statusLabels[salary.status] || salary.status }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <Card><p class="text-xs text-slate-500">Hari Dibayar</p><p class="mt-1 text-xl font-bold">{{ salary.paid_days }} hari</p></Card>
                <Card><p class="text-xs text-slate-500">Gaji Harian</p><p class="mt-1 text-xl font-bold text-blue-600">{{ formatCurrency(salary.daily_rate) }}</p></Card>
                <Card><p class="text-xs text-slate-500">Gaji Pokok</p><p class="mt-1 text-xl font-bold text-emerald-600">{{ formatCurrency(salary.base_salary) }}</p></Card>
                <Card><p class="text-xs text-slate-500">Status Bayar</p><p class="mt-1 text-xl font-bold">{{ statusLabels[salary.status] || salary.status }}</p></Card>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="font-bold">Komponen Gaji</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex justify-between py-3"><dt>Gaji Pokok</dt><dd class="font-semibold">{{ formatCurrency(salary.base_salary) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Lembur</dt><dd class="font-semibold">{{ formatCurrency(salary.overtime_pay) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Bonus Kehadiran</dt><dd class="font-semibold">{{ formatCurrency(salary.attendance_bonus) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Bonus Kinerja</dt><dd class="font-semibold">{{ formatCurrency(salary.performance_bonus) }}</dd></div>
                        <div class="flex justify-between py-3 text-red-600"><dt>Potongan</dt><dd class="font-semibold">- {{ formatCurrency(salary.deductions) }}</dd></div>
                        <div class="flex justify-between py-4 text-emerald-700"><dt class="font-bold">Total</dt><dd class="text-lg font-bold">{{ formatCurrency(salary.total_salary) }}</dd></div>
                    </dl>
                </Card>

                <Card>
                    <h2 class="font-bold">Kehadiran Periode Ini</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex justify-between py-3"><dt>Hari Periode</dt><dd class="font-semibold">{{ salary.working_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt>Hadir</dt><dd class="font-semibold text-emerald-600">{{ salary.present_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt>Terlambat</dt><dd class="font-semibold text-amber-600">{{ salary.late_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt>Tidak Hadir</dt><dd class="font-semibold text-red-600">{{ salary.absent_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt>Cuti</dt><dd class="font-semibold text-violet-600">{{ salary.leave_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt>Libur</dt><dd class="font-semibold">{{ salary.holiday_days }} hari</dd></div>
                    </dl>
                    <div class="mt-5">
                        <div class="flex justify-between text-sm font-semibold"><span>Persentase hari dibayar</span><span>{{ salary.attendance_percentage }}%</span></div>
                        <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${salary.attendance_percentage}%` }" />
                        </div>
                    </div>
                </Card>
            </section>

            <Card v-if="salary.notes">
                <p class="text-sm font-semibold text-slate-500">Catatan</p>
                <p class="mt-2 text-sm text-slate-700">{{ salary.notes }}</p>
            </Card>

            <div class="flex justify-end">
                <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</Link>
            </div>
        </div>
    </AppShell>
</template>
