<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    salaries: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
});

const filters = reactive({
    period: props.filters.period || '',
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const statusLabels = { draft: 'Draft', calculated: 'Dihitung', paid: 'Dibayar' };
const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-700 ring-slate-200',
    calculated: 'bg-amber-50 text-amber-700 ring-amber-200',
    paid: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');

const applyFilter = () => {
    router.get(props.links.index, filters, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Gaji Saya" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Karyawan</p>
                    <h1 class="text-2xl font-bold text-slate-950">Gaji Saya</h1>
                    <p class="mt-1 text-sm text-slate-500">Pantau riwayat gaji dan rincian pembayaran setiap periode.</p>
                </div>
                <form class="flex gap-2" @submit.prevent="applyFilter">
                    <select v-model="filters.period" class="rounded-lg border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        <option value="">Semua Periode</option>
                        <option v-for="period in options.periods" :key="period.value" :value="period.value">{{ period.label }}</option>
                    </select>
                    <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                </form>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Total Slip</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats.total || 0 }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Total Dibayar</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">{{ formatCurrency(stats.paid) }}</p>
                </Card>
                <Card>
                    <p class="text-xs font-semibold text-slate-500">Rata-rata Gaji</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">{{ formatCurrency(stats.average) }}</p>
                </Card>
            </section>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Hari Dibayar</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Gaji Harian</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="salary in salaries.data" :key="salary.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3"><span class="rounded-md bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ salary.period }}</span></td>
                                <td class="px-4 py-3 text-sm">{{ salary.paid_days }} hari</td>
                                <td class="px-4 py-3 text-sm">{{ formatCurrency(salary.daily_rate) }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-emerald-700">{{ formatCurrency(salary.total_salary) }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusClass(salary.status)">{{ statusLabels[salary.status] || salary.status }}</span></td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="salary.urls.show" class="rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="salaries.data.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data gaji</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="salary in salaries.data" :key="salary.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-900">{{ salary.period }}</p>
                                <p class="text-sm text-slate-500">{{ salary.paid_days }} hari dibayar</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusClass(salary.status)">{{ statusLabels[salary.status] || salary.status }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <p><span class="block text-xs text-slate-400">Gaji Harian</span><b>{{ formatCurrency(salary.daily_rate) }}</b></p>
                            <p><span class="block text-xs text-slate-400">Total</span><b class="text-emerald-700">{{ formatCurrency(salary.total_salary) }}</b></p>
                        </div>
                        <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                            <Link :href="salary.urls.show" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Detail</Link>
                        </div>
                    </article>
                    <div v-if="salaries.data.length === 0" class="rounded-lg border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-400">
                        Belum ada data gaji
                    </div>
                </div>

                <div class="mt-5">
                    <Pagination :links="salaries.links" />
                </div>
            </Card>
        </div>
    </AppShell>
</template>
