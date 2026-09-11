<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import DataTable from '../../../Components/DataTable.vue';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    links: { type: Object, required: true },
});

const search = ref('');
const status = ref('');
const confirmState = ref({ show: false, title: '', message: '', action: null });

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const activePercentage = computed(() => {
    if (!props.stats.total) return 0;
    return Math.round((props.stats.active / props.stats.total) * 100);
});

const filteredEmployees = computed(() => props.employees.filter((employee) => {
    const name = employee.user?.name?.toLowerCase() || '';
    const email = employee.user?.email?.toLowerCase() || '';
    const term = search.value.toLowerCase();
    return (name.includes(term) || email.includes(term) || employee.employee_code.toLowerCase().includes(term))
        && (status.value === '' || employee.status === status.value);
}));

const columns = [
    { key: 'employee', label: 'Karyawan' },
    { key: 'code', label: 'Kode' },
    { key: 'position', label: 'Posisi' },
    { key: 'salary', label: 'Gaji' },
    { key: 'attendance', label: 'Absen Hari Ini' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const statusLabel = (value) => ({
    active: 'Aktif',
    inactive: 'Tidak Aktif',
    resigned: 'Resign',
}[value] || value);

const attendanceLabel = (value) => ({
    present: 'Hadir',
    late: 'Terlambat',
    absent: 'Tidak Hadir',
    half_day: 'Setengah Hari',
    leave: 'Cuti',
    auto_checkout: 'Auto Checkout',
    not_started: 'Belum Absen',
}[value] || value);

const askDelete = (employee) => {
    confirmState.value = {
        show: true,
        title: 'Hapus Karyawan',
        message: `Apakah Anda yakin ingin menghapus ${employee.user?.name}? Akun login karyawan juga akan dihapus.`,
        action: () => router.delete(employee.urls.destroy, { preserveScroll: true }),
    };
};

const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
</script>

<template>
    <Head title="Manajemen Karyawan" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Owner</p>
                    <h1 class="text-2xl font-bold text-slate-950">Manajemen Karyawan</h1>
                    <p class="mt-1 text-sm text-slate-500">Kelola akun login, profil kerja, gaji harian, dan status karyawan.</p>
                </div>
                <Link :href="links.create" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Tambah Karyawan
                </Link>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <p class="text-sm font-medium text-slate-500">Total Karyawan</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total }}</p>
                </Card>
                <Card>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Karyawan Aktif</p>
                            <p class="mt-2 text-3xl font-bold">{{ stats.active }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ activePercentage }}%</span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${activePercentage}%` }" />
                    </div>
                </Card>
                <Card>
                    <p class="text-sm font-medium text-slate-500">Rata-rata Gaji Harian</p>
                    <p class="mt-2 truncate text-2xl font-bold">{{ formatCurrency(stats.average_daily_rate) }}</p>
                </Card>
            </section>

            <Card>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-bold">Daftar Karyawan</h2>
                        <p class="mt-1 text-sm text-slate-500">Cari dan filter data karyawan.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input v-model="search" type="text" placeholder="Cari nama, email, kode..." class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100 sm:w-72">
                        <select v-model="status" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100 sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="resigned">Resign</option>
                        </select>
                    </div>
                </div>
            </Card>

            <div class="hidden md:block">
                <DataTable :columns="columns" :rows="filteredEmployees" empty-text="Karyawan tidak ditemukan">
                    <template #employee="{ row }">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">
                                {{ row.user?.name?.charAt(0) || 'K' }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-bold text-slate-900">{{ row.user?.name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ row.user?.email }}</p>
                            </div>
                        </div>
                    </template>
                    <template #code="{ row }">
                        <span class="rounded-md bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold">{{ row.employee_code }}</span>
                    </template>
                    <template #position="{ row }">{{ row.position || '-' }}</template>
                    <template #salary="{ row }">
                        <p class="font-bold text-emerald-600">{{ formatCurrency(row.daily_rate) }}</p>
                        <p class="text-xs text-slate-400">per hari</p>
                    </template>
                    <template #attendance="{ row }">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="row.today_attendance ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200'">
                            {{ attendanceLabel(row.today_attendance?.status || 'not_started') }}
                        </span>
                        <p v-if="row.today_attendance?.check_in_time" class="mt-1 text-xs text-slate-500">
                            {{ row.today_attendance.check_in_time }}<template v-if="row.today_attendance.check_out_time"> - {{ row.today_attendance.check_out_time }}</template>
                        </p>
                    </template>
                    <template #status="{ row }">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="row.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : row.status === 'inactive' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ statusLabel(row.status) }}
                        </span>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex justify-end gap-2">
                            <Link :href="row.urls.show" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Detail</Link>
                            <Link :href="row.urls.calendar" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50">Kalender</Link>
                            <Link :href="row.urls.edit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                            <button type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="askDelete(row)">Hapus</button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <section class="space-y-4 md:hidden">
                <article v-for="employee in filteredEmployees" :key="employee.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-bold text-slate-900">{{ employee.user?.name }}</p>
                            <p class="truncate text-sm text-slate-500">{{ employee.user?.email }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="employee.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : employee.status === 'inactive' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ statusLabel(employee.status) }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                        <p><span class="block text-xs text-slate-400">Kode</span><b>{{ employee.employee_code }}</b></p>
                        <p><span class="block text-xs text-slate-400">Posisi</span><b>{{ employee.position || '-' }}</b></p>
                        <p><span class="block text-xs text-slate-400">Gaji</span><b>{{ formatCurrency(employee.daily_rate) }}</b></p>
                        <p><span class="block text-xs text-slate-400">Absensi</span><b>{{ attendanceLabel(employee.today_attendance?.status || 'not_started') }}</b></p>
                    </div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
                        <Link :href="employee.urls.show" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Detail</Link>
                        <Link :href="employee.urls.calendar" class="rounded-lg bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700">Kalender</Link>
                        <Link :href="employee.urls.edit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Edit</Link>
                        <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" @click="askDelete(employee)">Hapus</button>
                    </div>
                </article>
                <div v-if="filteredEmployees.length === 0" class="rounded-lg border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                    Karyawan tidak ditemukan
                </div>
            </section>
        </div>

        <ConfirmDialog
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            confirm-text="Hapus"
            @cancel="confirmState.show = false"
            @confirm="runConfirmed"
        />
    </AppShell>
</template>
