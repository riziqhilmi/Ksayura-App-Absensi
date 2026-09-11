<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import DataTable from '../../../Components/DataTable.vue';

const props = defineProps({
    shifts: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    links: { type: Object, required: true },
});

const search = ref('');
const status = ref('');
const confirmState = ref({ show: false, title: '', message: '', action: null, confirmText: 'Ya, lanjutkan' });

const activePercentage = computed(() => {
    if (!props.stats.total) return 0;
    return Math.round((props.stats.active / props.stats.total) * 100);
});

const filteredShifts = computed(() => props.shifts.filter((shift) => {
    const matchesSearch = shift.name.toLowerCase().includes(search.value.toLowerCase());
    const matchesStatus = status.value === '' || shift.status === status.value;
    return matchesSearch && matchesStatus;
}));

const columns = [
    { key: 'name', label: 'Nama Shift' },
    { key: 'start_time', label: 'Jam Mulai' },
    { key: 'end_time', label: 'Jam Selesai' },
    { key: 'break_time', label: 'Istirahat' },
    { key: 'grace_period', label: 'Toleransi' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const statusLabel = (value) => value === 'active' ? 'Aktif' : 'Nonaktif';

const askToggle = (shift) => {
    const target = shift.status === 'active' ? 'menonaktifkan' : 'mengaktifkan';
    confirmState.value = {
        show: true,
        title: 'Ubah Status Shift',
        message: `Apakah Anda yakin ingin ${target} shift ${shift.name}?`,
        confirmText: shift.status === 'active' ? 'Nonaktifkan' : 'Aktifkan',
        action: () => router.get(shift.urls.toggle_status, {}, { preserveScroll: true }),
    };
};

const askDelete = (shift) => {
    confirmState.value = {
        show: true,
        title: 'Hapus Shift',
        message: `Apakah Anda yakin ingin menghapus shift ${shift.name}?`,
        confirmText: 'Hapus',
        action: () => router.delete(shift.urls.destroy, { preserveScroll: true }),
    };
};

const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
</script>

<template>
    <Head title="Master Shift" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Master Data</p>
                    <h1 class="text-2xl font-bold text-slate-950">Manajemen Shift</h1>
                    <p class="mt-1 text-sm text-slate-500">Kelola jam kerja, waktu istirahat, toleransi keterlambatan, dan status shift.</p>
                </div>
                <Link :href="links.create" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Tambah Shift
                </Link>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <p class="text-sm font-medium text-slate-500">Total Shift</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total }}</p>
                </Card>
                <Card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Shift Aktif</p>
                            <p class="mt-2 text-3xl font-bold">{{ stats.active }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ activePercentage }}%</span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${activePercentage}%` }" />
                    </div>
                </Card>
                <Card>
                    <p class="text-sm font-medium text-slate-500">Shift Nonaktif</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.inactive }}</p>
                </Card>
            </section>

            <Card>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-bold">Daftar Shift</h2>
                        <p class="mt-1 text-sm text-slate-500">Cari, filter, dan kelola shift.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input v-model="search" type="text" placeholder="Cari nama shift..." class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100 sm:w-72">
                        <select v-model="status" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100 sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </Card>

            <div class="hidden md:block">
                <DataTable :columns="columns" :rows="filteredShifts" empty-text="Shift tidak ditemukan">
                    <template #name="{ row }">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-sm font-bold text-white">
                                {{ row.name.charAt(0) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-bold text-slate-900">{{ row.name }}</p>
                                <p v-if="row.notes" class="truncate text-xs text-slate-500">{{ row.notes }}</p>
                            </div>
                        </div>
                    </template>
                    <template #start_time="{ row }">
                        <span class="rounded-md bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ row.start_time }}</span>
                    </template>
                    <template #end_time="{ row }">
                        <span class="rounded-md bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ row.end_time }}</span>
                    </template>
                    <template #break_time="{ row }">
                        <span>{{ row.break_start && row.break_end ? `${row.break_start} - ${row.break_end}` : '-' }}</span>
                    </template>
                    <template #grace_period="{ row }">
                        <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ row.grace_period }} menit</span>
                    </template>
                    <template #status="{ row }">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="row.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ statusLabel(row.status) }}
                        </span>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex justify-end gap-2">
                            <Link :href="row.urls.show" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Detail</Link>
                            <Link :href="row.urls.edit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                            <button type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50" @click="askToggle(row)">
                                {{ row.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            <button type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="askDelete(row)">Hapus</button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <section class="space-y-4 md:hidden">
                <article v-for="shift in filteredShifts" :key="shift.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-900">{{ shift.name }}</p>
                            <p v-if="shift.notes" class="mt-1 text-sm text-slate-500">{{ shift.notes }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="shift.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ statusLabel(shift.status) }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                        <p><span class="block text-xs text-slate-400">Mulai</span><b>{{ shift.start_time }}</b></p>
                        <p><span class="block text-xs text-slate-400">Selesai</span><b>{{ shift.end_time }}</b></p>
                        <p><span class="block text-xs text-slate-400">Istirahat</span><b>{{ shift.break_start && shift.break_end ? `${shift.break_start} - ${shift.break_end}` : '-' }}</b></p>
                        <p><span class="block text-xs text-slate-400">Toleransi</span><b>{{ shift.grace_period }} menit</b></p>
                    </div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
                        <Link :href="shift.urls.show" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Detail</Link>
                        <Link :href="shift.urls.edit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Edit</Link>
                        <button type="button" class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700" @click="askToggle(shift)">
                            {{ shift.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" @click="askDelete(shift)">Hapus</button>
                    </div>
                </article>
                <div v-if="filteredShifts.length === 0" class="rounded-lg border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                    Shift tidak ditemukan
                </div>
            </section>
        </div>

        <ConfirmDialog
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            :confirm-text="confirmState.confirmText"
            @cancel="confirmState.show = false"
            @confirm="runConfirmed"
        />
    </AppShell>
</template>
