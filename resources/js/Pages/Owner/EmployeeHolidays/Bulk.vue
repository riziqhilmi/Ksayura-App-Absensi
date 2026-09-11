<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
});

const search = ref('');
const form = useForm({
    employee_ids: [],
    date: '',
    reason: '',
    type: 'company',
    is_paid: true,
});

const filteredEmployees = computed(() => props.employees.filter((employee) => {
    const term = search.value.toLowerCase();
    return (employee.name || '').toLowerCase().includes(term)
        || (employee.employee_code || '').toLowerCase().includes(term);
}));

const toggleAll = () => {
    form.employee_ids = form.employee_ids.length === filteredEmployees.value.length
        ? []
        : filteredEmployees.value.map((employee) => employee.id);
};

const submit = () => form.post(props.links.store, { preserveScroll: true });
</script>

<template>
    <Head title="Bulk Hari Libur" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-bold text-slate-950">Bulk Hari Libur</h1>
                <p class="mt-1 text-sm text-slate-500">Tambahkan hari libur untuk banyak karyawan sekaligus.</p>
            </div>

            <Card>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Tanggal</label>
                            <input v-model="form.date" type="date" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <p v-if="form.errors.date" class="mt-1 text-xs text-red-600">{{ form.errors.date }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Jenis</label>
                            <select v-model="form.type" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <option v-for="type in options.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input v-model="form.is_paid" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Libur dibayar
                    </label>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Alasan</label>
                        <input v-model="form.reason" type="text" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                    </div>

                    <div class="rounded-lg border border-slate-200">
                        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <input v-model="search" type="text" placeholder="Cari karyawan..." class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700" @click="toggleAll">
                                {{ form.employee_ids.length === filteredEmployees.length ? 'Kosongkan' : 'Pilih Semua' }}
                            </button>
                        </div>
                        <div class="grid max-h-80 grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2">
                            <label v-for="employee in filteredEmployees" :key="employee.id" class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 text-sm">
                                <input v-model="form.employee_ids" type="checkbox" :value="employee.id" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span><b>{{ employee.name }}</b><span class="block text-xs text-slate-500">{{ employee.employee_code }}</span></span>
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.employee_ids" class="text-xs text-red-600">{{ form.errors.employee_ids }}</p>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Batal</Link>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Bulk' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
