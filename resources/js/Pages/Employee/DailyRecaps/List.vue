<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    recaps: { type: Object, required: true },
    filters: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = reactive({
    date: props.filters.date || '',
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
    <Head title="Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-6xl space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Karyawan</p>
                    <h1 class="text-2xl font-black text-slate-950">Rekap Harian</h1>
                    <p class="mt-1 text-sm text-slate-500">Riwayat rekap semua karyawan, lengkap dengan siapa yang membuka dan menutup rekap.</p>
                </div>
                <Link :href="links.create" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                    Tambah Rekap
                </Link>
            </div>

            <Card>
                <form class="grid gap-3 sm:grid-cols-[1fr_140px]" @submit.prevent="applyFilter">
                    <input v-model="form.date" type="date" :max="filters.today" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60" :disabled="loading">
                        {{ loading ? 'Memfilter...' : 'Filter' }}
                    </button>
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Oleh</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
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
                                    <b class="text-slate-900">{{ recap.recapped_by?.name || recap.employee?.name || '-' }}</b>
                                    <span class="block text-xs text-slate-500">{{ recap.recapped_by?.employee_code || recap.employee?.employee_code || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="recap.is_open ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200'">
                                        {{ recap.is_open ? 'Masih aktif' : 'Ditutup' }}
                                    </span>
                                    <span class="mt-1 block text-xs text-slate-500">
                                        Dibuka {{ recap.opened_by?.name || '-' }}<template v-if="recap.closed_by">, ditutup {{ recap.closed_by.name }}</template>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.total_expense_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.total_qris_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ formatRupiah(recap.remaining_cash_amount) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-black text-emerald-700">{{ formatRupiah(recap.capital_amount) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="recap.urls.show" class="rounded-lg px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Detail</Link>
                                    <Link v-if="recap.urls.edit" :href="recap.urls.edit" class="rounded-lg px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="recaps.data.length === 0">
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada rekap harian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="recap in recaps.data" :key="recap.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black text-slate-900">{{ recap.recap_date_long }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Direkap {{ recap.recapped_by?.name || recap.employee?.name || '-' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ recap.expenses_count }} item pengeluaran - {{ recap.qris_transactions_count }} QRIS</p>
                            </div>
                            <p class="text-right text-sm font-black text-emerald-700">{{ formatRupiah(recap.capital_amount) }}</p>
                        </div>
                        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                            <b :class="recap.is_open ? 'text-emerald-700' : 'text-slate-700'">{{ recap.is_open ? 'Masih aktif' : 'Ditutup' }}</b>
                            <span class="block">Dibuka oleh {{ recap.opened_by?.name || '-' }}<template v-if="recap.closed_by">, ditutup oleh {{ recap.closed_by.name }}</template></span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-xs">
                            <p><span class="block text-slate-400">Pengeluaran</span><b>{{ formatRupiah(recap.total_expense_amount) }}</b></p>
                            <p><span class="block text-slate-400">QRIS</span><b>{{ formatRupiah(recap.total_qris_amount) }}</b></p>
                            <p><span class="block text-slate-400">Sisa</span><b>{{ formatRupiah(recap.remaining_cash_amount) }}</b></p>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Link :href="recap.urls.show" class="flex-1 rounded-lg bg-blue-50 px-3 py-2 text-center text-xs font-bold text-blue-700">Detail</Link>
                            <Link v-if="recap.urls.edit" :href="recap.urls.edit" class="flex-1 rounded-lg bg-emerald-50 px-3 py-2 text-center text-xs font-bold text-emerald-700">Edit</Link>
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
