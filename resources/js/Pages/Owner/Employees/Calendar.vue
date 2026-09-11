<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import WorkCalendar from '../../../Components/WorkCalendar.vue';

const props = defineProps({
    employee: { type: Object, required: true },
    calendarData: { type: Array, default: () => [] },
    monthName: { type: String, required: true },
    firstDayOfMonth: { type: Number, required: true },
    stats: { type: Object, required: true },
    links: { type: Object, required: true },
});

const workingPercent = computed(() => props.stats.total_days ? Math.round((props.stats.working_days / props.stats.total_days) * 100) : 0);
const statCards = [
    ['Total Hari', 'total_days', 'text-slate-900'],
    ['Hari Kerja', 'working_days', 'text-emerald-600'],
    ['Hadir', 'present_days', 'text-emerald-600'],
    ['Terlambat', 'late_days', 'text-amber-600'],
    ['Libur/Cuti', 'holidays', 'text-violet-600'],
    ['Akhir Pekan', 'weekends', 'text-slate-600'],
    ['Tidak Hadir', 'absent_days', 'text-red-600'],
];
</script>

<template>
    <Head title="Kalender Karyawan" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex justify-end">
                <Link href="/owner/employees" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Kembali ke Karyawan</Link>
            </div>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-5 bg-gradient-to-r from-blue-600 via-cyan-600 to-emerald-500 p-6 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 text-xl font-bold">
                            {{ employee.name?.charAt(0) || 'K' }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white/80">Kalender {{ monthName }}</p>
                            <h1 class="truncate text-2xl font-bold">{{ employee.name || '-' }}</h1>
                            <p class="truncate text-sm text-white/80">{{ employee.employee_code || '-' }} | {{ employee.position || 'Staff' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Link :href="links.previous" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 hover:bg-white/25">‹</Link>
                        <Link :href="links.today" class="flex h-10 items-center rounded-xl bg-white px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">Hari Ini</Link>
                        <Link :href="links.next" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 hover:bg-white/25">›</Link>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                <Card v-for="item in statCards" :key="item[1]">
                    <p class="text-xs font-bold uppercase text-slate-400">{{ item[0] }}</p>
                    <p class="mt-1 text-2xl font-bold" :class="item[2]">{{ stats[item[1]] || 0 }}</p>
                    <div v-if="item[1] === 'working_days'" class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${workingPercent}%` }" />
                    </div>
                </Card>
            </section>

            <WorkCalendar :days="calendarData" :first-day-of-month="firstDayOfMonth" />
        </div>
    </AppShell>
</template>
