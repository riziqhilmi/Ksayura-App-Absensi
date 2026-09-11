<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    holiday: { type: Object, default: null },
    employees: { type: Array, default: () => [] },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.holiday?.employee_id || '',
    date: props.holiday?.date || '',
    reason: props.holiday?.reason || '',
    type: props.holiday?.type || 'annual',
    is_paid: props.holiday?.is_paid ?? true,
    status: props.holiday?.status || 'scheduled',
    notes: props.holiday?.notes || '',
});

const submit = () => {
    if (props.holiday) {
        form.put(props.links.update, { preserveScroll: true });
        return;
    }

    form.post(props.links.store, { preserveScroll: true });
};
</script>

<template>
    <Head :title="holiday ? 'Edit Hari Libur' : 'Tambah Hari Libur'" />

    <AppShell>
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-bold text-slate-950">{{ holiday ? 'Edit Hari Libur' : 'Tambah Hari Libur' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Atur hari libur individual karyawan.</p>
            </div>

            <Card>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Karyawan</label>
                            <select v-model="form.employee_id" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <option value="">Pilih Karyawan</option>
                                <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                            </select>
                            <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
                        </div>
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
                        <div v-if="holiday">
                            <label class="text-sm font-semibold text-slate-700">Status</label>
                            <select v-model="form.status" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <option v-for="status in options.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
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

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Catatan</label>
                        <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Batal</Link>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
