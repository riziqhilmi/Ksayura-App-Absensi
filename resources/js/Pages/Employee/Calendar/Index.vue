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

const workingPercent = computed(() => {
    if (!props.stats.total_days) return 0;
    return Math.round((props.stats.working_days / props.stats.total_days) * 100);
});

const statCards = [
    ['Total Hari', 'total_days', 'text-slate-900'],
    ['Hari Kerja', 'working_days', 'text-emerald-600'],
    ['Hadir', 'present_days', 'text-emerald-600'],
    ['Terlambat', 'late_days', 'text-amber-600'],
    ['Libur', 'holidays', 'text-violet-600'],
    ['Akhir Pekan', 'weekends', 'text-slate-600'],
    ['Tidak Hadir', 'absent_days', 'text-red-600'],
];
</script>

<template>
    <Head title="Kalender Kerja Saya" />

    <AppShell>
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-5 bg-gradient-to-r from-blue-600 via-cyan-600 to-emerald-500 p-6 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Kalender Kerja</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ monthName }}</h1>
                        <p class="mt-1 text-sm text-white/80">{{ employee.name }}</p>
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

            <Card>
                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Shift/Hadir</span>
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700">Terlambat</span>
                    <span class="rounded-full bg-violet-50 px-3 py-1.5 text-violet-700">Libur/Cuti</span>
                    <span class="rounded-full bg-red-50 px-3 py-1.5 text-red-700">Tidak Hadir</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700">Hari Ini</span>
                </div>
            </Card>

            <WorkCalendar :days="calendarData" :first-day-of-month="firstDayOfMonth" />
        </div>
    </AppShell>
</template>
