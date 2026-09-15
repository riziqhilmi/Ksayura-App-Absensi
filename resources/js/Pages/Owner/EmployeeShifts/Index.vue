<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import DataTable from '../../../Components/DataTable.vue';
import Modal from '../../../Components/Modal.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    employeeShifts: { type: Object, required: true },
    employees: { type: Array, default: () => [] },
    rollingEmployees: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    links: { type: Object, required: true },
});

const filterForm = reactive({
    employee: props.filters.employee ?? '',
    status: props.filters.status ?? '',
});

const confirmState = reactive({
    show: false,
    title: '',
    message: '',
    action: null,
});
const rollingOpen = ref(false);
const rollingForm = useForm({
    from_employee_shift_id: '',
    to_employee_shift_id: '',
    start_date: '',
    end_date: '',
});

const columns = [
    { key: 'employee', label: 'Karyawan' },
    { key: 'shift', label: 'Shift' },
    { key: 'day', label: 'Hari' },
    { key: 'period', label: 'Periode' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const applyFilters = () => {
    router.get(props.links.index, filterForm, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.employee = '';
    filterForm.status = '';
    applyFilters();
};

const selectedFrom = computed(() =>
    props.rollingEmployees.find((employee) => String(employee.assignment_id) === String(rollingForm.from_employee_shift_id))
);

const selectedTo = computed(() =>
    props.rollingEmployees.find((employee) => String(employee.assignment_id) === String(rollingForm.to_employee_shift_id))
);

const rollingDateReady = computed(() => Boolean(rollingForm.start_date && rollingForm.end_date));

const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

const dateRange = computed(() => {
    if (!rollingDateReady.value) return [];

    const dates = [];
    const current = new Date(`${rollingForm.start_date}T00:00:00`);
    const end = new Date(`${rollingForm.end_date}T00:00:00`);

    if (Number.isNaN(current.getTime()) || Number.isNaN(end.getTime()) || current > end) {
        return [];
    }

    while (current <= end) {
        const year = current.getFullYear();
        const month = String(current.getMonth() + 1).padStart(2, '0');
        const dayOfMonth = String(current.getDate()).padStart(2, '0');
        const date = `${year}-${month}-${dayOfMonth}`;

        dates.push({
            date,
            day: dayNames[current.getDay()],
        });
        current.setDate(current.getDate() + 1);
    }

    return dates;
});

const assignmentActiveOn = (assignment, day) => {
    const assignmentStart = assignment.start_date || '1000-01-01';
    const assignmentEnd = assignment.end_date || '9999-12-31';
    const inDateRange = assignmentStart <= day.date && assignmentEnd >= day.date;
    const inDay = !assignment.day_of_week || assignment.day_of_week === day.day;

    return inDateRange && inDay;
};

const assignmentMatchesRollingDates = (assignment) => {
    if (!rollingDateReady.value) return false;

    return dateRange.value.some((day) => assignmentActiveOn(assignment, day));
};

const rollingOptions = computed(() =>
    props.rollingEmployees.filter((assignment) => assignmentMatchesRollingDates(assignment))
);

watch(
    () => [rollingForm.start_date, rollingForm.end_date],
    () => {
        const optionIds = rollingOptions.value.map((assignment) => String(assignment.assignment_id));

        if (!optionIds.includes(String(rollingForm.from_employee_shift_id))) {
            rollingForm.from_employee_shift_id = '';
        }

        if (!optionIds.includes(String(rollingForm.to_employee_shift_id))) {
            rollingForm.to_employee_shift_id = '';
        }
    }
);

const openRolling = () => {
    rollingForm.clearErrors();
    rollingForm.reset();
    rollingOpen.value = true;
};

const submitRolling = () => {
    rollingForm.post(props.links.rollingShift, {
        preserveScroll: true,
        onSuccess: () => {
            rollingForm.reset();
            rollingOpen.value = false;
        },
    });
};

const askDelete = (employeeShift) => {
    confirmState.show = true;
    confirmState.title = 'Hapus Penugasan Shift';
    confirmState.message = `Apakah Anda yakin ingin menghapus penugasan ${employeeShift.employee_name}?`;
    confirmState.action = () => router.delete(employeeShift.urls.destroy, { preserveScroll: true });
};

const runConfirmed = () => {
    const action = confirmState.action;
    confirmState.show = false;
    if (action) action();
};
</script>

<template>
    <Head title="Penugasan Shift" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Operasional</p>
                    <h1 class="text-2xl font-bold text-slate-950">Penugasan Shift Karyawan</h1>
                    <p class="mt-1 text-sm text-slate-500">Atur shift aktif, hari kerja khusus, periode berlaku, dan pola berulang.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" @click="openRolling">
                        Rolling Shift
                    </button>
                    <Link :href="links.create" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        Assign Shift
                    </Link>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <p class="text-sm font-medium text-slate-500">Total Penugasan</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total }}</p>
                </Card>
                <Card>
                    <p class="text-sm font-medium text-slate-500">Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.active }}</p>
                </Card>
                <Card>
                    <p class="text-sm font-medium text-slate-500">Tidak Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-red-600">{{ stats.inactive }}</p>
                </Card>
            </section>

            <Card>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-base font-bold">Filter Penugasan</h2>
                        <p class="mt-1 text-sm text-slate-500">Filter berdasarkan karyawan dan status.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,260px)_180px_auto_auto]">
                        <select v-model="filterForm.employee" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                            <option value="">Semua Karyawan</option>
                            <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.label }}</option>
                        </select>
                        <select v-model="filterForm.status" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                        <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" @click="applyFilters">Terapkan</button>
                        <button type="button" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="clearFilters">Reset</button>
                    </div>
                </div>
            </Card>

            <div class="hidden md:block">
                <DataTable :columns="columns" :rows="employeeShifts.data" empty-text="Belum ada penugasan shift">
                    <template #employee="{ row }">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">
                                {{ row.employee_name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ row.employee_name }}</p>
                                <p class="text-xs text-slate-500">{{ row.employee_code }}</p>
                            </div>
                        </div>
                    </template>
                    <template #shift="{ row }">
                        <p class="font-bold text-slate-900">{{ row.shift_name }}</p>
                        <p class="text-xs text-slate-500">{{ row.shift_time }}</p>
                    </template>
                    <template #day="{ row }">
                        <span>{{ row.day_label }}</span>
                        <span v-if="row.is_recurring" class="ml-1 text-xs font-semibold text-blue-600">(Berulang)</span>
                    </template>
                    <template #period="{ row }">{{ row.period_label }}</template>
                    <template #status="{ row }">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="row.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ row.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex justify-end gap-2">
                            <Link :href="row.urls.edit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                            <button type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="askDelete(row)">Hapus</button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <section class="space-y-4 md:hidden">
                <article v-for="item in employeeShifts.data" :key="item.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-900">{{ item.employee_name }}</p>
                            <p class="text-sm text-slate-500">{{ item.employee_code }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="item.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'">
                            {{ item.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                        <p><span class="block text-xs text-slate-400">Shift</span><b>{{ item.shift_name }}</b></p>
                        <p><span class="block text-xs text-slate-400">Jam</span><b>{{ item.shift_time || '-' }}</b></p>
                        <p><span class="block text-xs text-slate-400">Hari</span><b>{{ item.day_label }}</b></p>
                        <p><span class="block text-xs text-slate-400">Periode</span><b>{{ item.period_label }}</b></p>
                    </div>
                    <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-3">
                        <Link :href="item.urls.edit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Edit</Link>
                        <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" @click="askDelete(item)">Hapus</button>
                    </div>
                </article>
                <div v-if="employeeShifts.data.length === 0" class="rounded-lg border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                    Belum ada penugasan shift
                </div>
            </section>

            <Pagination :links="employeeShifts.links" />
        </div>

        <ConfirmDialog
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            confirm-text="Hapus"
            @cancel="confirmState.show = false"
            @confirm="runConfirmed"
        />

        <Modal :show="rollingOpen" title="Rolling Shift" @close="rollingOpen = false">
            <form class="space-y-5" @submit.prevent="submitRolling">
                <p class="text-sm text-slate-500">
                    Pilih tanggal dulu. Daftar karyawan hanya berisi yang punya shift aktif pada tanggal tersebut.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Tanggal Mulai</span>
                        <input v-model="rollingForm.start_date" type="date" required class="mt-1 w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        <span v-if="rollingForm.errors.start_date" class="mt-1 block text-xs font-semibold text-red-600">{{ rollingForm.errors.start_date }}</span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Tanggal Selesai</span>
                        <input v-model="rollingForm.end_date" type="date" required class="mt-1 w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        <span v-if="rollingForm.errors.end_date" class="mt-1 block text-xs font-semibold text-red-600">{{ rollingForm.errors.end_date }}</span>
                    </label>
                </div>

                <p v-if="rollingDateReady && rollingOptions.length === 0" class="rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">
                    Tidak ada karyawan yang memiliki shift aktif pada tanggal yang dipilih.
                </p>
                <p v-else-if="!rollingDateReady" class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">
                    Pilih tanggal mulai dan selesai terlebih dahulu, lalu pilih karyawan yang akan rolling.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Dari Karyawan</span>
                        <select v-model="rollingForm.from_employee_shift_id" required class="mt-1 w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" :disabled="!rollingDateReady">
                            <option value="">Pilih penugasan</option>
                            <option
                                v-for="employee in rollingOptions"
                                :key="`from-${employee.assignment_id}`"
                                :value="employee.assignment_id"
                                :disabled="String(employee.assignment_id) === String(rollingForm.to_employee_shift_id)"
                            >
                                {{ employee.label }}
                            </option>
                        </select>
                        <span v-if="rollingForm.errors.from_employee_shift_id" class="mt-1 block text-xs font-semibold text-red-600">{{ rollingForm.errors.from_employee_shift_id }}</span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Ke Karyawan</span>
                        <select v-model="rollingForm.to_employee_shift_id" required class="mt-1 w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" :disabled="!rollingDateReady">
                            <option value="">Pilih penugasan</option>
                            <option
                                v-for="employee in rollingOptions"
                                :key="`to-${employee.assignment_id}`"
                                :value="employee.assignment_id"
                                :disabled="String(employee.assignment_id) === String(rollingForm.from_employee_shift_id)"
                            >
                                {{ employee.label }}
                            </option>
                        </select>
                        <span v-if="rollingForm.errors.to_employee_shift_id" class="mt-1 block text-xs font-semibold text-red-600">{{ rollingForm.errors.to_employee_shift_id }}</span>
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase text-slate-400">Shift saat ini</p>
                        <p class="mt-1 font-bold text-slate-900">{{ selectedFrom?.employee_name || '-' }}</p>
                        <p class="text-sm text-slate-500">{{ selectedFrom ? `${selectedFrom.shift_name} (${selectedFrom.shift_time || '-'})` : 'Pilih penugasan pertama' }}</p>
                        <p v-if="selectedFrom" class="mt-1 text-xs text-slate-400">{{ selectedFrom.day_label }} | {{ selectedFrom.period_label }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase text-slate-400">Ditukar dengan</p>
                        <p class="mt-1 font-bold text-slate-900">{{ selectedTo?.employee_name || '-' }}</p>
                        <p class="text-sm text-slate-500">{{ selectedTo ? `${selectedTo.shift_name} (${selectedTo.shift_time || '-'})` : 'Pilih penugasan kedua' }}</p>
                        <p v-if="selectedTo" class="mt-1 text-xs text-slate-400">{{ selectedTo.day_label }} | {{ selectedTo.period_label }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200" @click="rollingOpen = false">Batal</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="rollingForm.processing || !rollingForm.from_employee_shift_id || !rollingForm.to_employee_shift_id || !rollingForm.start_date || !rollingForm.end_date">
                        {{ rollingForm.processing ? 'Memproses...' : 'Buat Rolling Sementara' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AppShell>
</template>
