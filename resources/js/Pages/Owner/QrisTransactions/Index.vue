<script setup>
import { reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    transactions: { type: Object, required: true },
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
</script>

<template>
    <Head title="Monitoring QRIS" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-black text-slate-950">Monitoring QRIS</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau transaksi QRIS yang dicatat karyawan sepanjang hari.</p>
            </div>

            <section class="grid gap-4 sm:grid-cols-2">
                <Card><p class="text-sm font-medium text-slate-500">Transaksi</p><p class="mt-2 text-3xl font-black">{{ stats.count || 0 }}</p></Card>
                <Card><p class="text-sm font-medium text-slate-500">Total QRIS</p><p class="mt-2 text-3xl font-black text-emerald-700">{{ formatRupiah(stats.total_qris_amount) }}</p></Card>
            </section>

            <Card>
                <form class="grid gap-3 md:grid-cols-[1fr_1fr_140px]" @submit.prevent="applyFilter">
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
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Karyawan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Nominal</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm">{{ transaction.created_at }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <b>{{ transaction.employee?.name || '-' }}</b>
                                    <span class="block text-xs text-slate-500">{{ transaction.employee?.employee_code || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-black text-emerald-700">{{ formatRupiah(transaction.amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a v-if="transaction.evidence_url" :href="transaction.evidence_url" target="_blank" class="font-bold text-blue-700">Lihat</a>
                                    <span v-else class="text-slate-400">Tidak tersedia</span>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada transaksi QRIS.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="transaction in transactions.data" :key="transaction.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black">{{ transaction.employee?.name || '-' }}</p>
                                <p class="text-xs text-slate-500">{{ transaction.created_at }}</p>
                            </div>
                            <p class="text-right text-sm font-black text-emerald-700">{{ formatRupiah(transaction.amount) }}</p>
                        </div>
                        <a v-if="transaction.evidence_url" :href="transaction.evidence_url" target="_blank" class="mt-3 inline-block text-sm font-bold text-blue-700">Lihat bukti</a>
                        <p v-else class="mt-3 text-xs font-semibold text-slate-400">Foto tidak tersedia</p>
                    </article>
                    <div v-if="transactions.data.length === 0" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                        Belum ada transaksi QRIS.
                    </div>
                </div>

                <div class="mt-5">
                    <Pagination :links="transactions.links" />
                </div>
            </Card>
        </div>
    </AppShell>
</template>
