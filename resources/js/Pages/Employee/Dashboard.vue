<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../Components/AppShell.vue';
import Card from '../../Components/Card.vue';

const props = defineProps({
    employee: { type: Object, required: true },
    todayAttendance: { type: Object, default: null },
    stats: { type: Object, required: true },
    pendingLeaves: { type: Number, default: 0 },
    latestSalary: { type: Object, default: null },
    recentActivities: { type: Array, default: () => [] },
    quickLinks: { type: Object, required: true },
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const todayStatus = computed(() => {
    if (!props.todayAttendance) return 'Belum Absen';
    if (props.todayAttendance.check_in_time && !props.todayAttendance.check_out_time) return 'Checked In';
    if (props.todayAttendance.check_out_time) return 'Complete';
    return props.todayAttendance.status || 'Belum Absen';
});
</script>

<template>
    <Head title="Dashboard Karyawan" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg bg-emerald-700 p-6 text-white shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-emerald-100">Selamat datang kembali</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ employee.user?.name || employee.full_name || 'Karyawan' }}</h1>
                        <p class="mt-1 text-sm text-emerald-100">{{ employee.position || 'Staff' }} - {{ employee.employee_code }}</p>
                    </div>
                    <div class="rounded-lg bg-white/15 px-4 py-3 text-sm font-semibold ring-1 ring-white/20">
                        Status hari ini: {{ todayStatus }}
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Kehadiran</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats.attendance_percentage }}%</p>
                </Card>
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Hadir</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">{{ stats.present }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Pending Cuti</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ pendingLeaves }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Gaji Terakhir</p>
                    <p class="mt-2 truncate text-lg font-bold text-blue-600">{{ formatCurrency(latestSalary?.total_salary) }}</p>
                </Card>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <a :href="quickLinks.attendance" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                    <p class="font-bold text-slate-900">Absensi</p>
                    <p class="mt-1 text-sm text-slate-500">Lihat riwayat dan status absensi.</p>
                </a>
                <a :href="quickLinks.leaveCreate" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                    <p class="font-bold text-slate-900">Ajukan Cuti</p>
                    <p class="mt-1 text-sm text-slate-500">Buat pengajuan cuti baru.</p>
                </a>
                <a :href="quickLinks.calendar" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                    <p class="font-bold text-slate-900">Kalender Kerja</p>
                    <p class="mt-1 text-sm text-slate-500">Lihat jadwal shift dan hari libur.</p>
                </a>
            </section>

            <Card>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Aktivitas Terbaru</h2>
                        <p class="text-sm text-slate-500">Ringkasan aktivitas pribadi terakhir.</p>
                    </div>
                    <a :href="quickLinks.attendance" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        Detail
                    </a>
                </div>

                <div v-if="recentActivities.length" class="divide-y divide-slate-100 rounded-lg border border-slate-100">
                    <div v-for="(activity, index) in recentActivities" :key="index" class="flex gap-3 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-700">
                            {{ activity.title?.charAt(0) || 'A' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900">{{ activity.title }}</p>
                            <p class="text-sm text-slate-500">{{ activity.description }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ activity.time }}</span>
                    </div>
                </div>
                <div v-else class="rounded-lg border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-400">
                    Belum ada aktivitas
                </div>
            </Card>
        </div>
    </AppShell>
</template>
