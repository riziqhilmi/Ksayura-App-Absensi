<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    salary: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = useForm({
    base_salary: props.salary.base_salary || 0,
    overtime_pay: props.salary.overtime_pay || 0,
    attendance_bonus: props.salary.attendance_bonus || 0,
    performance_bonus: props.salary.performance_bonus || 0,
    deductions: props.salary.deductions || 0,
    notes: props.salary.notes || '',
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const totalSalary = computed(() => (
    Number(form.base_salary || 0)
    + Number(form.overtime_pay || 0)
    + Number(form.attendance_bonus || 0)
    + Number(form.performance_bonus || 0)
    - Number(form.deductions || 0)
));

const submit = () => form.put(props.links.update, { preserveScroll: true });
</script>

<template>
    <Head title="Edit Gaji" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-bold text-slate-950">Edit Gaji</h1>
                <p class="mt-1 text-sm text-slate-500">{{ salary.employee?.name || '-' }} | Periode {{ salary.period }}</p>
            </div>

            <Card>
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div v-for="field in [
                            ['base_salary', 'Total Gaji Harian'],
                            ['overtime_pay', 'Lembur'],
                            ['attendance_bonus', 'Bonus Kehadiran'],
                            ['performance_bonus', 'Bonus Kinerja'],
                            ['deductions', 'Potongan'],
                        ]" :key="field[0]">
                            <label class="block text-sm font-semibold text-slate-700">{{ field[1] }}</label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-2.5 text-sm text-slate-500">Rp</span>
                                <input v-model="form[field[0]]" type="number" min="0" class="w-full rounded-lg border-slate-200 py-2.5 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </div>
                            <p v-if="form.errors[field[0]]" class="mt-1 text-xs text-red-600">{{ form.errors[field[0]] }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Catatan</label>
                        <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <div class="rounded-lg bg-emerald-50 p-4">
                        <p class="text-sm font-semibold text-emerald-700">Estimasi Total Gaji</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-800">{{ formatCurrency(totalSalary) }}</p>
                        <p class="mt-1 text-xs text-emerald-700">{{ salary.paid_days }} hari x {{ formatCurrency(salary.daily_rate) }}</p>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link :href="links.show" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Batal</Link>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Gaji' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
