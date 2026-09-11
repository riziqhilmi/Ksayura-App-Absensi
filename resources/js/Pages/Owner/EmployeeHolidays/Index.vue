<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    holidays: { type: Object, required: true },
    employees: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = reactive({
    employee: props.filters.employee || '',
    month: props.filters.month,
    year: props.filters.year,
    status: props.filters.status || '',
});
const confirmState = ref({ show: false, title: '', message: '', action: null });

const applyFilter = () => router.get(props.links.index, form, { preserveState: true, replace: true });
const askDelete = (holiday) => {
    confirmState.value = {
        show: true,
        title: 'Hapus Hari Libur',
        message: `Hapus hari libur ${holiday.employee?.name || '-'} tanggal ${holiday.date_label}?`,
        action: () => router.delete(holiday.urls.destroy, { preserveScroll: true }),
    };
};
const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
const badgeClass = (status) => ({
    scheduled: 'bg-amber-50 text-amber-700 ring-amber-200',
    taken: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    cancelled: 'bg-red-50 text-red-700 ring-red-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');
</script>

<template>
    <Head title="Hari Libur Karyawan" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Owner</p>
                    <h1 class="text-2xl font-bold text-slate-950">Hari Libur Karyawan</h1>
                    <p class="mt-1 text-sm text-slate-500">Kelola jadwal libur personal, libur perusahaan, dan cuti yang dibayar.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="links.calendar" class="rounded-lg bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-100">Kalender</Link>
                    <Link :href="links.bulk" class="rounded-lg bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 hover:bg-violet-100">Bulk</Link>
                    <Link :href="links.create" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Tambah Libur</Link>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <Card><p class="text-sm text-slate-500">Total</p><p class="mt-2 text-3xl font-bold">{{ stats.total }}</p></Card>
                <Card><p class="text-sm text-slate-500">Terjadwal</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ stats.scheduled }}</p></Card>
                <Card><p class="text-sm text-slate-500">Diambil</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.taken }}</p></Card>
                <Card><p class="text-sm text-slate-500">Akan Datang</p><p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.upcoming }}</p></Card>
            </section>

            <Card>
                <form class="grid grid-cols-1 gap-3 md:grid-cols-5" @submit.prevent="applyFilter">
                    <select v-model="form.employee" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Karyawan</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                    </select>
                    <select v-model="form.month" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option v-for="month in options.months" :key="month.value" :value="month.value">{{ month.label }}</option>
                    </select>
                    <select v-model="form.year" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option v-for="year in options.years" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <select v-model="form.status" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Status</option>
                        <option v-for="status in options.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                    </select>
                    <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Alasan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="holiday in holidays.data" :key="holiday.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold">{{ holiday.date_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ holiday.employee?.name || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ holiday.type_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ holiday.reason || '-' }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="badgeClass(holiday.status)">{{ holiday.status_label }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="holiday.urls.edit" class="rounded-lg px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                                        <button type="button" class="rounded-lg px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50" @click="askDelete(holiday)">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="holidays.data.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada hari libur</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="holiday in holidays.data" :key="holiday.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ holiday.employee?.name || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ holiday.date_label }} | {{ holiday.type_label }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="badgeClass(holiday.status)">{{ holiday.status_label }}</span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">{{ holiday.reason || 'Tanpa alasan' }}</p>
                        <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-3">
                            <Link :href="holiday.urls.edit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Edit</Link>
                            <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" @click="askDelete(holiday)">Hapus</button>
                        </div>
                    </article>
                </div>

                <div class="mt-5">
                    <Pagination :links="holidays.links" />
                </div>
            </Card>
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
