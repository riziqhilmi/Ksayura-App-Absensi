<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    recaps: { type: Object, required: true },
    employees: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = reactive({
    date: props.filters.date || '',
    employee: props.filters.employee || '',
});

const loading = ref(false);

const toInt = (value) => Math.max(0, Number(value || 0));
const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(toInt(value));

const applyFilter = () => {
    loading.value = true;
    router.get(props.links.index, form, {
        preserveState: true,
        replace: true,
        onFinish: () => { loading.value = false; },
    });
};

const resetFilter = () => {
    form.date = '';
    form.employee = '';
    applyFilter();
};
</script>

<template>
    <Head title="Manajemen Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-black text-slate-950">Manajemen Rekap Harian</h1>
                <p class="mt-1 text-sm text-slate-500">Lihat rekap berdasarkan tanggal dan karyawan, termasuk nominal QRIS permanen.</p>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card><p class="text-sm font-medium text-slate-500">Rekap</p><p class="mt-2 text-3xl font-black">{{ stats.total_recaps || 0 }}</p></Card>
                <Card><p class="text-sm font-medium text-slate-500">Pengeluaran</p><p class="mt-2 text-2xl font-black text-red-600">{{ formatRupiah(stats.total_expense_amount) }}</p></Card>
                <Card><p class="text-sm font-medium text-slate-500">QRIS</p><p class="mt-2 text-2xl font-black text-blue-600">{{ formatRupiah(stats.total_qris_amount) }}</p></Card>
                <Card><p class="text-sm font-medium text-slate-500">Modal</p><p class="mt-2 text-2xl font-black text-emerald-700">{{ formatRupiah(stats.total_capital_amount) }}</p></Card>
            </section>

            <Card>
                <form class="grid gap-3 md:grid-cols-[1fr_1fr_140px_120px]" @submit.prevent="applyFilter">
                    <input v-model="form.date" type="date" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                    <select v-model="form.employee" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Karyawan</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                            {{ employee.name }} - {{ employee.employee_code }}
                        </option>
                    </select>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="loading">
                        {{ loading ? 'Memfilter...' : 'Filter' }}
                    </button>
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200 disabled:opacity-60" :disabled="loading" @click="resetFilter">
                        Reset
                    </button>
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Karyawan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Pengeluaran</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">QRIS</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Sisa</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Modal</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="recap in recaps.data" :key="recap.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold">{{ recap.recap_date_label }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <b>{{ recap.employee?.name || '-' }}</b>
                                    <span class="block text-xs text-slate-500">{{ recap.employee?.employee_code || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.total_expense_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.total_qris_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.remaining_cash_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-black text-emerald-700">{{ formatRupiah(recap.capital_amount) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="recap.urls.edit" class="rounded-lg px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                                    <Link :href="recap.urls.show" class="rounded-lg px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="recaps.data.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada rekap harian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="recap in recaps.data" :key="recap.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black">{{ recap.employee?.name || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ recap.recap_date_long }}</p>
                            </div>
                            <p class="text-right text-sm font-black text-emerald-700">{{ formatRupiah(recap.capital_amount) }}</p>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-xs">
                            <p><span class="block text-slate-400">Pengeluaran</span><b>{{ formatRupiah(recap.total_expense_amount) }}</b></p>
                            <p><span class="block text-slate-400">QRIS</span><b>{{ formatRupiah(recap.total_qris_amount) }}</b></p>
                            <p><span class="block text-slate-400">Sisa</span><b>{{ formatRupiah(recap.remaining_cash_amount) }}</b></p>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <Link :href="recap.urls.edit" class="rounded-lg bg-emerald-50 px-3 py-2 text-center text-xs font-bold text-emerald-700">Edit</Link>
                            <Link :href="recap.urls.show" class="rounded-lg bg-blue-50 px-3 py-2 text-center text-xs font-bold text-blue-700">Detail</Link>
                        </div>
                    </article>
                    <div v-if="recaps.data.length === 0" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                        Belum ada rekap harian.
                    </div>
                </div>

                <div class="mt-5">
                    <Pagination :links="recaps.links" />
                </div>
            </Card>
        </div>
    </AppShell>
</template>
