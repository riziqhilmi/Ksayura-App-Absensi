<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    calendarData: { type: Array, default: () => [] },
    monthName: { type: String, required: true },
    firstDayOfMonth: { type: Number, required: true },
    stats: { type: Object, required: true },
    links: { type: Object, required: true },
});

const selectedDate = ref(props.calendarData.find((day) => day.is_today)?.date || props.calendarData[0]?.date || null);

const selectedDay = computed(() => props.calendarData.find((day) => day.date === selectedDate.value) || null);

const leadingBlanks = computed(() => Array.from({ length: props.firstDayOfMonth }));

const statusLabels = {
    empty: 'Kosong',
    pending: 'Pending',
    approved: 'Approved',
};

const statusClass = (status) => ({
    empty: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    pending: 'border-amber-200 bg-amber-50 text-amber-800',
    approved: 'border-red-200 bg-red-50 text-red-800',
}[status] || 'border-slate-200 bg-slate-50 text-slate-700');

const dotClass = (status) => ({
    empty: 'bg-emerald-500',
    pending: 'bg-amber-500',
    approved: 'bg-red-500',
}[status] || 'bg-slate-300');

const entryStatusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    approved: 'bg-red-50 text-red-700 ring-red-200',
}[status] || 'bg-slate-50 text-slate-700 ring-slate-200');
</script>

<template>
    <Head title="Kalender Libur Tim" />

    <AppShell>
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-5 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 p-5 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Karyawan</p>
                        <h1 class="mt-1 text-2xl font-bold">Kalender Libur Tim</h1>
                        <p class="mt-1 text-sm text-white/80">{{ monthName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Link :href="links.previous" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 text-lg font-bold hover:bg-white/25">&lt;</Link>
                        <Link :href="links.today" class="flex h-10 items-center rounded-lg bg-white px-4 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Hari Ini</Link>
                        <Link :href="links.next" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 text-lg font-bold hover:bg-white/25">&gt;</Link>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-3 gap-3">
                <Card>
                    <p class="text-xs font-bold uppercase text-slate-400">Kosong</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ stats.empty_days || 0 }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-bold uppercase text-slate-400">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ stats.pending_days || 0 }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-bold uppercase text-slate-400">Approved</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">{{ stats.approved_days || 0 }}</p>
                </Card>
            </section>

            <Card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Hijau: kosong</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700">Kuning: pending</span>
                        <span class="rounded-full bg-red-50 px-3 py-1.5 text-red-700">Merah: approved</span>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="links.create" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Ajukan Cuti</Link>
                        <Link :href="links.leaves" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Daftar Saya</Link>
                    </div>
                </div>
            </Card>

            <section class="grid gap-4 lg:grid-cols-[1fr_22rem]">
                <Card>
                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-bold uppercase text-slate-400 sm:text-xs">
                        <span>Min</span>
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                    </div>

                    <div class="mt-2 grid grid-cols-7 gap-1.5 sm:gap-2">
                        <div v-for="(_, index) in leadingBlanks" :key="`blank-${index}`" class="aspect-square rounded-lg bg-slate-50" />
                        <button
                            v-for="day in calendarData"
                            :key="day.date"
                            type="button"
                            class="relative flex aspect-square min-h-14 flex-col justify-between rounded-lg border p-2 text-left transition hover:ring-2 hover:ring-emerald-200"
                            :class="[
                                statusClass(day.status),
                                selectedDate === day.date ? 'ring-2 ring-slate-900' : '',
                            ]"
                            @click="selectedDate = day.date"
                        >
                            <span class="flex items-start justify-between gap-1">
                                <span class="text-sm font-bold sm:text-base">{{ day.day }}</span>
                                <span v-if="day.is_today" class="h-1.5 w-1.5 rounded-full bg-sky-500" />
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full" :class="dotClass(day.status)" />
                                <span class="hidden truncate text-[11px] font-semibold sm:block">{{ statusLabels[day.status] }}</span>
                            </span>
                            <span v-if="day.entries.length > 0" class="absolute bottom-1 right-1 rounded-full bg-white/80 px-1.5 text-[10px] font-bold text-slate-700">
                                {{ day.entries.length }}
                            </span>
                        </button>
                    </div>
                </Card>

                <Card>
                    <div v-if="selectedDay" class="space-y-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ selectedDay.date }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" :class="dotClass(selectedDay.status)" />
                                <h2 class="text-lg font-bold text-slate-950">{{ statusLabels[selectedDay.status] }}</h2>
                            </div>
                        </div>

                        <div v-if="selectedDay.entries.length > 0" class="space-y-3">
                            <article v-for="(entry, index) in selectedDay.entries" :key="`${entry.employee_id}-${entry.status}-${index}`" class="rounded-lg border border-slate-200 p-3">
                                <p class="font-semibold text-slate-900">{{ entry.employee_name }}</p>
                                <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="entryStatusClass(entry.status)">
                                    {{ entry.status }}
                                </span>
                            </article>
                        </div>
                        <div v-else class="rounded-lg border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                            Belum ada jadwal libur atau pengajuan pending karyawan lain pada tanggal ini.
                        </div>
                    </div>
                </Card>
            </section>
        </div>
    </AppShell>
</template>
