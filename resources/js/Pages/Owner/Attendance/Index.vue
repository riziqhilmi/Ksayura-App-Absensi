<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    attendances: { type: Object, required: true },
    employees: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = reactive({
    date: props.filters.date || '',
    employee: props.filters.employee || '',
    status: props.filters.status || '',
});

const statusLabels = {
    present: 'Hadir',
    late: 'Terlambat',
    absent: 'Tidak Hadir',
    half_day: 'Setengah Hari',
    leave: 'Cuti',
    auto_checkout: 'Auto Check Out',
};

const statCards = [
    ['Total', 'total', 'text-slate-900', 'bg-slate-100'],
    ['Hadir', 'present', 'text-emerald-600', 'bg-emerald-100'],
    ['Terlambat', 'late', 'text-amber-600', 'bg-amber-100'],
    ['Tidak Hadir', 'absent', 'text-red-600', 'bg-red-100'],
    ['Cuti', 'leave', 'text-violet-600', 'bg-violet-100'],
];

const loading = ref(false);

const applyFilter = () => {
    loading.value = true;
    router.get(props.links.index, form, {
        preserveState: true,
        replace: true,
        onFinish: () => { loading.value = false; },
    });
};

const openLocation = (lat, lng) => {
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
};

const badgeClass = (status) => ({
    present: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    late: 'bg-amber-50 text-amber-700 ring-amber-200',
    absent: 'bg-red-50 text-red-700 ring-red-200',
    half_day: 'bg-blue-50 text-blue-700 ring-blue-200',
    leave: 'bg-violet-50 text-violet-700 ring-violet-200',
    auto_checkout: 'bg-orange-50 text-orange-700 ring-orange-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');
</script>

<template>
    <Head title="Monitoring Absensi" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Owner</p>
                    <h1 class="text-2xl font-bold text-slate-950">Monitoring Absensi</h1>
                    <p class="mt-1 text-sm text-slate-500">Pantau kehadiran, lokasi check-in, dan status absensi karyawan.</p>
                </div>
                <a :href="links.export" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Export
                </a>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <Card v-for="item in statCards" :key="item[1]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ item[0] }}</p>
                            <p class="mt-2 text-3xl font-bold" :class="item[2]">{{ stats[item[1]] || 0 }}</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl" :class="item[3]" />
                    </div>
                </Card>
            </section>

            <Card>
                <form class="grid grid-cols-1 gap-3 md:grid-cols-4" @submit.prevent="applyFilter">
                    <input v-model="form.date" type="date" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                    <select v-model="form.employee" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Karyawan</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                    </select>
                    <select v-model="form.status" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Status</option>
                        <option value="present">Hadir</option>
                        <option value="late">Terlambat</option>
                        <option value="absent">Tidak Hadir</option>
                        <option value="half_day">Setengah Hari</option>
                        <option value="leave">Cuti</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="loading">
                        {{ loading ? 'Memfilter...' : 'Filter' }}
                    </button>
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Check In</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Check Out</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="attendance in attendances.data" :key="attendance.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm">{{ attendance.date_label }}</td>
                                <td class="px-4 py-3 text-sm font-semibold">{{ attendance.employee?.name || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ attendance.check_in_time || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ attendance.check_out_time || '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <button v-if="attendance.latitude_in && attendance.longitude_in" type="button" class="font-semibold text-blue-700 hover:text-blue-800" @click="openLocation(attendance.latitude_in, attendance.longitude_in)">Lihat Lokasi</button>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="badgeClass(attendance.status)">
                                        {{ statusLabels[attendance.status] || attendance.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="attendance.urls.show" class="rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data absensi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="attendance in attendances.data" :key="attendance.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ attendance.employee?.name || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ attendance.date_label }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="badgeClass(attendance.status)">
                                {{ statusLabels[attendance.status] || attendance.status }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <p><span class="block text-xs text-slate-400">Masuk</span><b>{{ attendance.check_in_time || '-' }}</b></p>
                            <p><span class="block text-xs text-slate-400">Pulang</span><b>{{ attendance.check_out_time || '-' }}</b></p>
                            <p><span class="block text-xs text-slate-400">Shift</span><b>{{ attendance.shift?.name || '-' }}</b></p>
                            <Link :href="attendance.urls.show" class="font-bold text-blue-700">Detail</Link>
                        </div>
                    </article>
                    <div v-if="attendances.data.length === 0" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                        Belum ada data absensi
                    </div>
                </div>

                <div class="mt-5">
                    <Pagination :links="attendances.links" />
                </div>
            </Card>
        </div>
    </AppShell>
</template>
