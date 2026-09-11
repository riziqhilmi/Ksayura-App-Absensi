<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

defineProps({
    shift: { type: Object, required: true },
    links: { type: Object, required: true },
});
</script>

<template>
    <Head title="Detail Shift" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <section class="rounded-lg bg-emerald-700 p-6 text-white shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/15 text-2xl font-bold ring-1 ring-white/20">
                            {{ shift.name.charAt(0) }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ shift.name }}</h1>
                            <p class="mt-1 text-sm text-emerald-100">{{ shift.notes || 'Tidak ada catatan' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-white/20" :class="shift.status === 'active' ? 'bg-white/15 text-white' : 'bg-red-500/20 text-white'">
                        {{ shift.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="text-lg font-bold text-slate-900">Waktu Shift</h2>
                    <div class="mt-4 divide-y divide-slate-100 text-sm">
                        <p class="flex justify-between py-3"><span class="text-slate-500">Jam Mulai</span><b>{{ shift.start_time }}</b></p>
                        <p class="flex justify-between py-3"><span class="text-slate-500">Jam Selesai</span><b>{{ shift.end_time }}</b></p>
                        <p class="flex justify-between py-3"><span class="text-slate-500">Durasi</span><b>{{ shift.duration_hours }} jam</b></p>
                    </div>
                </Card>

                <Card>
                    <h2 class="text-lg font-bold text-slate-900">Istirahat & Toleransi</h2>
                    <div class="mt-4 divide-y divide-slate-100 text-sm">
                        <template v-if="shift.break_start && shift.break_end">
                            <p class="flex justify-between py-3"><span class="text-slate-500">Mulai Istirahat</span><b>{{ shift.break_start }}</b></p>
                            <p class="flex justify-between py-3"><span class="text-slate-500">Selesai Istirahat</span><b>{{ shift.break_end }}</b></p>
                            <p class="flex justify-between py-3"><span class="text-slate-500">Durasi Istirahat</span><b>{{ shift.break_duration_minutes }} menit</b></p>
                        </template>
                        <p v-else class="py-5 text-center text-slate-400">Tidak ada waktu istirahat</p>
                        <p class="flex justify-between py-3"><span class="text-slate-500">Toleransi Keterlambatan</span><b>{{ shift.grace_period }} menit</b></p>
                    </div>
                </Card>
            </section>

            <Card>
                <h2 class="text-lg font-bold text-slate-900">Informasi Lainnya</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <p class="flex justify-between rounded-lg bg-slate-50 px-4 py-3"><span class="text-slate-500">Dibuat</span><b>{{ shift.created_at }}</b></p>
                    <p class="flex justify-between rounded-lg bg-slate-50 px-4 py-3"><span class="text-slate-500">Terakhir Diupdate</span><b>{{ shift.updated_at }}</b></p>
                </div>
            </Card>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Link :href="links.index" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali
                </Link>
                <Link :href="links.edit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Edit Shift
                </Link>
            </div>
        </div>
    </AppShell>
</template>
