<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../Components/AppShell.vue';
import Card from '../../Components/Card.vue';
import DataTable from '../../Components/DataTable.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    recentActivities: { type: Array, default: () => [] },
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const activePercentage = computed(() => {
    const total = Number(props.stats.total_employees || 0);
    if (!total) return 0;
    return Math.round((Number(props.stats.active_employees || 0) / total) * 100);
});

const monthlyRows = computed(() => {
    const monthly = props.stats.monthly_attendance || {};
    const total = Number(monthly.total || 0);
    return [
        { label: 'Hadir', value: monthly.present || 0, color: 'bg-emerald-500', text: 'text-emerald-600' },
        { label: 'Terlambat', value: monthly.late || 0, color: 'bg-amber-500', text: 'text-amber-600' },
        { label: 'Tidak Hadir', value: monthly.absent || 0, color: 'bg-red-500', text: 'text-red-600' },
        { label: 'Setengah Hari / Auto CO', value: (monthly.half_day || 0) + (monthly.auto_checkout || 0), color: 'bg-indigo-500', text: 'text-indigo-600' },
    ].map((row) => ({
        ...row,
        width: total ? Math.min(100, Math.round((row.value / total) * 100)) : 0,
    }));
});

const activityColumns = [
    { key: 'user', label: 'Nama' },
    { key: 'action', label: 'Aktivitas' },
    { key: 'time', label: 'Waktu' },
];
</script>

<template>
    <Head title="Dashboard Owner" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="overflow-hidden rounded-lg bg-emerald-700 shadow-sm">
                <div class="flex flex-col gap-4 px-6 py-6 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-100">Selamat datang kembali</p>
                        <h1 class="mt-1 text-2xl font-bold">Dashboard Owner</h1>
                        <p class="mt-2 text-sm text-emerald-100">Ringkasan operasional Kantor Sayur hari ini.</p>
                    </div>
                    <div class="rounded-lg bg-white/15 px-4 py-3 text-sm font-semibold ring-1 ring-white/20">
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }) }}
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Card>
                    <p class="text-sm font-medium text-slate-500">Total Karyawan</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total_employees }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-700"><b>{{ stats.active_employees }}</b><br>Aktif</div>
                        <div class="rounded-lg bg-amber-50 p-2 text-amber-700"><b>{{ stats.inactive_employees }}</b><br>Nonaktif</div>
                        <div class="rounded-lg bg-blue-50 p-2 text-blue-700"><b>+{{ stats.new_employees }}</b><br>Baru</div>
                    </div>
                </Card>

                <Card>
                    <p class="text-sm font-medium text-slate-500">Kehadiran Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.today_attendance }}</p>
                    <p class="mt-3 text-sm text-slate-500"><b class="text-emerald-600">{{ stats.attendance_percentage }}%</b> dari karyawan aktif</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-700"><b>{{ stats.today_present }}</b><br>Hadir</div>
                        <div class="rounded-lg bg-amber-50 p-2 text-amber-700"><b>{{ stats.today_late }}</b><br>Telat</div>
                        <div class="rounded-lg bg-red-50 p-2 text-red-700"><b>{{ stats.today_absent }}</b><br>Absen</div>
                    </div>
                </Card>

                <Card>
                    <p class="text-sm font-medium text-slate-500">Estimasi Gaji Bulanan</p>
                    <p class="mt-2 text-2xl font-bold">{{ formatCurrency(stats.total_salary) }}</p>
                    <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                        Rata-rata harian<br>
                        <b class="text-slate-900">{{ formatCurrency(stats.average_salary) }}</b>
                    </p>
                </Card>

                <Card>
                    <p class="text-sm font-medium text-slate-500">Pengajuan Cuti</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total_leaves }}</p>
                    <div class="mt-4 space-y-2 text-sm">
                        <p class="flex justify-between text-slate-600"><span>Menunggu</span><b class="text-amber-600">{{ stats.pending_leaves }}</b></p>
                        <p class="flex justify-between text-slate-600"><span>Disetujui</span><b class="text-emerald-600">{{ stats.approved_leaves }}</b></p>
                        <p class="flex justify-between text-slate-600"><span>Ditolak</span><b class="text-red-600">{{ stats.rejected_leaves }}</b></p>
                    </div>
                </Card>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <h2 class="text-lg font-bold">Status Karyawan</h2>
                    <p class="text-sm text-slate-500">Komposisi karyawan saat ini</p>
                    <div class="mt-5 flex items-center gap-5">
                        <div class="relative h-28 w-28 shrink-0">
                            <svg class="h-28 w-28 -rotate-90" viewBox="0 0 112 112">
                                <circle cx="56" cy="56" r="45" stroke="#e2e8f0" stroke-width="12" fill="none" />
                                <circle cx="56" cy="56" r="45" stroke="#10b981" stroke-width="12" fill="none" stroke-linecap="round" :stroke-dasharray="`${(activePercentage / 100) * 282.74} 282.74`" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold">{{ activePercentage }}%</span>
                                <span class="text-xs text-slate-500">Aktif</span>
                            </div>
                        </div>
                        <div class="w-full space-y-2 text-sm">
                            <p class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Aktif</span><b>{{ stats.active_employees }}</b></p>
                            <p class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Tidak Aktif</span><b>{{ stats.inactive_employees }}</b></p>
                            <p class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Resign</span><b>{{ stats.resigned_employees }}</b></p>
                        </div>
                    </div>
                </Card>

                <Card>
                    <h2 class="text-lg font-bold">Kehadiran Bulan Ini</h2>
                    <p class="text-sm text-slate-500">Total catatan: {{ stats.monthly_attendance?.total || 0 }}</p>
                    <div class="mt-5 space-y-4">
                        <div v-for="row in monthlyRows" :key="row.label">
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-medium text-slate-600">{{ row.label }}</span>
                                <span class="font-bold" :class="row.text">{{ row.value }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full" :class="row.color" :style="{ width: `${row.width}%` }" />
                            </div>
                        </div>
                    </div>
                </Card>
            </section>

            <Card>
                <div class="mb-4">
                    <h2 class="text-lg font-bold">Aktivitas Terbaru</h2>
                    <p class="text-sm text-slate-500">Update aktivitas absensi dan cuti terakhir.</p>
                </div>
                <DataTable :columns="activityColumns" :rows="recentActivities" empty-text="Belum ada aktivitas" />
            </Card>
        </div>
    </AppShell>
</template>
