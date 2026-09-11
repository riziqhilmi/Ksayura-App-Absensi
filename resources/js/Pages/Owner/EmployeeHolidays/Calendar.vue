<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    holidaysByEmployee: { type: Object, default: () => ({}) },
    month: { type: Number, required: true },
    year: { type: Number, required: true },
    daysInMonth: { type: Number, required: true },
    monthName: { type: String, required: true },
    today: { type: String, required: true },
    stats: { type: Object, required: true },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
});

const modalOpen = ref(false);
const processing = ref(false);
const notice = ref('');
const form = reactive({
    employee_id: '',
    date: '',
    type: 'company',
    reason: '',
    is_paid: true,
});

const days = computed(() => Array.from({ length: props.daysInMonth }, (_, index) => {
    const day = index + 1;
    const date = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return { day, date };
}));

const monthUrl = (offset) => {
    const date = new Date(props.year, props.month - 1 + offset, 1);
    const params = new URLSearchParams({ month: String(date.getMonth() + 1), year: String(date.getFullYear()) });
    return `${props.links.calendar}?${params.toString()}`;
};

const holidayFor = (employeeId, date) => (props.holidaysByEmployee[employeeId] || []).find((holiday) => holiday.date === date);
const typeClass = (holiday) => {
    if (!holiday) return '';
    if (holiday.status === 'taken') return 'bg-emerald-100 text-emerald-700';
    if (holiday.type === 'company') return 'bg-blue-100 text-blue-700';
    return 'bg-violet-100 text-violet-700';
};
const openCreate = (employee, date) => {
    form.employee_id = employee.id;
    form.date = date;
    form.type = 'company';
    form.reason = '';
    form.is_paid = true;
    notice.value = '';
    modalOpen.value = true;
};
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const submit = async () => {
    processing.value = true;
    notice.value = '';
    const response = await fetch(props.links.storeFromCalendar, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: JSON.stringify(form),
    });
    const data = await response.json();
    processing.value = false;
    if (!response.ok || !data.success) {
        notice.value = data.message || 'Gagal menambahkan hari libur';
        return;
    }
    modalOpen.value = false;
    router.reload({ preserveScroll: true });
};
</script>

<template>
    <Head title="Kalender Hari Libur" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Owner</p>
                    <h1 class="text-2xl font-bold text-slate-950">Kalender Hari Libur</h1>
                    <p class="mt-1 text-sm text-slate-500">Lihat dan tambahkan hari libur karyawan per bulan.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Daftar</Link>
                    <Link :href="monthUrl(-1)" class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Sebelumnya</Link>
                    <Link :href="monthUrl(0)" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">{{ monthName }}</Link>
                    <Link :href="monthUrl(1)" class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Berikutnya</Link>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <Card><p class="text-sm text-slate-500">Total</p><p class="mt-2 text-3xl font-bold">{{ stats.total }}</p></Card>
                <Card><p class="text-sm text-slate-500">Terjadwal</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ stats.scheduled }}</p></Card>
                <Card><p class="text-sm text-slate-500">Diambil</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.taken }}</p></Card>
                <Card><p class="text-sm text-slate-500">Akan Datang</p><p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.upcoming }}</p></Card>
            </section>

            <Card>
                <div class="overflow-x-auto">
                    <div class="min-w-[960px]">
                        <div class="grid border-b border-slate-200 bg-slate-50" :style="{ gridTemplateColumns: `220px repeat(${daysInMonth}, minmax(38px, 1fr))` }">
                            <div class="sticky left-0 z-10 bg-slate-50 px-3 py-3 text-xs font-bold uppercase text-slate-500">Karyawan</div>
                            <div v-for="day in days" :key="day.date" class="px-2 py-3 text-center text-xs font-bold text-slate-500" :class="day.date === today ? 'bg-blue-50 text-blue-700' : ''">
                                {{ day.day }}
                            </div>
                        </div>

                        <div v-for="employee in employees" :key="employee.id" class="grid border-b border-slate-100" :style="{ gridTemplateColumns: `220px repeat(${daysInMonth}, minmax(38px, 1fr))` }">
                            <div class="sticky left-0 z-10 bg-white px-3 py-3">
                                <p class="truncate text-sm font-bold text-slate-900">{{ employee.name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ employee.employee_code }}</p>
                            </div>
                            <button
                                v-for="day in days"
                                :key="`${employee.id}-${day.date}`"
                                type="button"
                                class="min-h-12 border-l border-slate-100 px-1 py-2 text-center transition hover:bg-emerald-50"
                                :class="day.date === today ? 'bg-blue-50' : ''"
                                @click="openCreate(employee, day.date)"
                            >
                                <span v-if="holidayFor(employee.id, day.date)" class="mx-auto flex max-w-[34px] items-center justify-center rounded-md px-1 py-1 text-[10px] font-bold" :class="typeClass(holidayFor(employee.id, day.date))">
                                    {{ holidayFor(employee.id, day.date).status === 'taken' ? 'OK' : 'Libur' }}
                                </span>
                                <span v-else class="text-slate-300">+</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <Modal :show="modalOpen" title="Tambah Hari Libur" @close="modalOpen = false">
            <form class="space-y-4" @submit.prevent="submit">
                <p v-if="notice" class="rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">{{ notice }}</p>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Karyawan</label>
                    <select v-model="form.employee_id" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm">
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Tanggal</label>
                    <input v-model="form.date" type="date" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Jenis</label>
                    <select v-model="form.type" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm">
                        <option v-for="type in options.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input v-model="form.is_paid" type="checkbox" class="rounded border-slate-300 text-emerald-600">
                    Libur dibayar
                </label>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Alasan</label>
                    <input v-model="form.reason" type="text" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700" @click="modalOpen = false">Batal</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AppShell>
</template>
