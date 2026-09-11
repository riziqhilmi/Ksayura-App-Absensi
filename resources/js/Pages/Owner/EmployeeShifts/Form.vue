<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import FormField from '../../../Components/FormField.vue';

const props = defineProps({
    mode: { type: String, required: true },
    employeeShift: { type: Object, default: null },
    employees: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
    links: { type: Object, required: true },
});

const isEdit = computed(() => props.mode === 'edit');

const form = useForm({
    employee_id: props.employeeShift?.employee_id ?? '',
    shift_id: props.employeeShift?.shift_id ?? '',
    start_date: props.employeeShift?.start_date ?? '',
    end_date: props.employeeShift?.end_date ?? '',
    day_of_week: props.employeeShift?.day_of_week ?? '',
    is_recurring: props.employeeShift?.is_recurring ?? false,
    status: props.employeeShift?.status ?? 'active',
    notes: props.employeeShift?.notes ?? '',
});

const days = [
    { value: '', label: 'Setiap Hari' },
    { value: 'monday', label: 'Senin' },
    { value: 'tuesday', label: 'Selasa' },
    { value: 'wednesday', label: 'Rabu' },
    { value: 'thursday', label: 'Kamis' },
    { value: 'friday', label: 'Jumat' },
    { value: 'saturday', label: 'Sabtu' },
    { value: 'sunday', label: 'Minggu' },
];

const submit = () => {
    if (isEdit.value) {
        form.put(props.links.submit, { preserveScroll: true });
        return;
    }

    form.post(props.links.submit, { preserveScroll: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Penugasan Shift' : 'Assign Shift'" />

    <AppShell>
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Penugasan Shift</p>
                <h1 class="text-2xl font-bold text-slate-950">{{ isEdit ? 'Edit Penugasan Shift' : 'Assign Shift ke Karyawan' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Pilih karyawan, shift aktif, periode berlaku, dan pola berulang bila diperlukan.</p>
            </div>

            <Card v-if="isEdit && employeeShift">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Penugasan saat ini</p>
                        <h2 class="text-lg font-bold text-slate-900">{{ employeeShift.employee_name }}</h2>
                        <p class="text-sm text-slate-500">{{ employeeShift.employee_code }} - {{ employeeShift.shift_name }}</p>
                    </div>
                    <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="employeeShift.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'">
                        {{ employeeShift.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </Card>

            <Card>
                <form class="space-y-6" @submit.prevent="submit">
                    <FormField label="Karyawan *" :error="form.errors.employee_id">
                        <select v-model="form.employee_id" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <option value="">Pilih Karyawan</option>
                            <option
                                v-for="employee in employees"
                                :key="employee.id"
                                :value="employee.id"
                                :disabled="!isEdit && employee.already_assigned"
                            >
                                {{ employee.label }}{{ !isEdit && employee.already_assigned ? ' - sudah punya shift' : '' }}
                            </option>
                        </select>
                        <span v-if="!isEdit" class="mt-1 block text-xs text-slate-500">Karyawan yang sudah punya penugasan shift aktif tidak bisa dipilih lagi.</span>
                    </FormField>

                    <FormField label="Shift *" :error="form.errors.shift_id">
                        <select v-model="form.shift_id" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <option value="">Pilih Shift</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.label }}</option>
                        </select>
                    </FormField>

                    <FormField label="Hari" :error="form.errors.day_of_week">
                        <select v-model="form.day_of_week" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <option v-for="day in days" :key="day.value" :value="day.value">{{ day.label }}</option>
                        </select>
                    </FormField>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <FormField label="Tanggal Mulai" :error="form.errors.start_date">
                            <input v-model="form.start_date" type="date" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        </FormField>
                        <FormField label="Tanggal Selesai" :error="form.errors.end_date">
                            <input v-model="form.end_date" type="date" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        </FormField>
                    </div>

                    <label class="flex items-center gap-3 rounded-lg bg-slate-50 px-4 py-3">
                        <input v-model="form.is_recurring" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Shift ini berulang setiap minggu</span>
                    </label>

                    <FormField v-if="isEdit" label="Status *" :error="form.errors.status">
                        <select v-model="form.status" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </FormField>

                    <FormField label="Catatan" :error="form.errors.notes">
                        <textarea v-model="form.notes" rows="3" placeholder="Tambahkan catatan jika diperlukan" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                    </FormField>

                    <div v-if="isEdit && employeeShift" class="rounded-lg bg-slate-50 px-4 py-3">
                        <h2 class="text-sm font-bold text-slate-700">Informasi Penugasan</h2>
                        <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                            <p><span class="text-slate-500">Dibuat:</span> <b>{{ employeeShift.created_at }}</b></p>
                            <p><span class="text-slate-500">Terakhir Update:</span> <b>{{ employeeShift.updated_at }}</b></p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <Link :href="links.index" class="inline-flex justify-center rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </Link>
                        <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Update Shift' : 'Simpan') }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
