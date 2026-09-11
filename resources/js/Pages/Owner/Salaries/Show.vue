<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';

const props = defineProps({
    salary: { type: Object, required: true },
    links: { type: Object, required: true },
});

const confirmState = ref({ show: false, title: '', message: '', action: null });
const processing = ref(false);
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const statusLabels = { draft: 'Draft', calculated: 'Siap Dibayar', paid: 'Sudah Dibayar' };
const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-700 ring-slate-200',
    calculated: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    paid: 'bg-blue-50 text-blue-700 ring-blue-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');

const askMarkPaid = () => {
    confirmState.value = {
        show: true,
        title: 'Tandai Dibayar',
        message: `Tandai gaji ${props.salary.employee?.name || '-'} sebagai sudah dibayar?`,
        action: markPaid,
    };
};
const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
const markPaid = async () => {
    processing.value = true;
    const response = await fetch(props.salary.urls.mark_paid, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
    });
    processing.value = false;
    if (response.ok) router.reload({ preserveScroll: true });
};
</script>

<template>
    <Head title="Detail Gaji" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <section class="rounded-2xl bg-gradient-to-r from-blue-600 to-emerald-500 p-6 text-white shadow-lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-50">Periode Gaji</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ salary.period }}</h1>
                        <p class="mt-1 text-sm text-blue-50">{{ salary.start_date_label }} - {{ salary.end_date_label }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-sm text-blue-50">Total Gaji</p>
                        <p class="text-3xl font-bold">{{ formatCurrency(salary.total_salary) }}</p>
                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="statusClass(salary.status)">
                            {{ statusLabels[salary.status] || salary.status }}
                        </span>
                    </div>
                </div>
            </section>

            <Card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-xl font-bold text-white">
                        {{ salary.employee?.name?.charAt(0) || 'K' }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">{{ salary.employee?.name || '-' }}</h2>
                        <p class="text-sm text-slate-500">{{ salary.employee?.employee_code || '-' }} | {{ salary.employee?.position || 'Staff' }}</p>
                        <p class="text-sm text-slate-500">{{ salary.employee?.email || '-' }}</p>
                    </div>
                </div>
            </Card>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <Card><p class="text-xs text-slate-500">Hari Dibayar</p><p class="mt-1 text-xl font-bold">{{ salary.paid_days }} hari</p></Card>
                <Card><p class="text-xs text-slate-500">Gaji Harian</p><p class="mt-1 text-xl font-bold text-blue-600">{{ formatCurrency(salary.daily_rate) }}</p></Card>
                <Card><p class="text-xs text-slate-500">Total Dibayar</p><p class="mt-1 text-xl font-bold text-emerald-600">{{ formatCurrency(salary.base_salary) }}</p></Card>
                <Card><p class="text-xs text-slate-500">Tidak Dibayar</p><p class="mt-1 text-xl font-bold text-red-600">{{ salary.absent_days + salary.leave_days + salary.holiday_days }} hari</p></Card>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="font-bold">Komponen Gaji</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex justify-between py-3"><dt>Gaji Harian</dt><dd class="font-semibold">{{ formatCurrency(salary.daily_rate) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Hari Dibayar</dt><dd class="font-semibold">{{ salary.paid_days }} hari</dd></div>
                        <div class="flex justify-between py-3 text-emerald-700"><dt>Gaji Pokok</dt><dd class="font-bold">{{ formatCurrency(salary.base_salary) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Lembur</dt><dd class="font-semibold">{{ formatCurrency(salary.overtime_pay) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Bonus Kehadiran</dt><dd class="font-semibold">{{ formatCurrency(salary.attendance_bonus) }}</dd></div>
                        <div class="flex justify-between py-3"><dt>Bonus Kinerja</dt><dd class="font-semibold">{{ formatCurrency(salary.performance_bonus) }}</dd></div>
                        <div class="flex justify-between py-3 text-red-600"><dt>Potongan</dt><dd class="font-semibold">- {{ formatCurrency(salary.deductions) }}</dd></div>
                        <div class="flex justify-between py-4 text-emerald-700"><dt class="font-bold">Total</dt><dd class="text-lg font-bold">{{ formatCurrency(salary.total_salary) }}</dd></div>
                    </dl>
                </Card>
                <Card>
                    <h2 class="font-bold">Statistik Kehadiran</h2>
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

            <div class="flex flex-wrap justify-end gap-3">
                <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</Link>
                <Link v-if="salary.status !== 'paid'" :href="salary.urls.edit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Edit Gaji</Link>
                <button v-if="salary.status === 'calculated'" type="button" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="processing" @click="askMarkPaid">
                    {{ processing ? 'Memproses...' : 'Tandai Dibayar' }}
                </button>
            </div>
        </div>

        <ConfirmDialog
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            confirm-text="Tandai Dibayar"
            @cancel="confirmState.show = false"
            @confirm="runConfirmed"
        />
    </AppShell>
</template>
