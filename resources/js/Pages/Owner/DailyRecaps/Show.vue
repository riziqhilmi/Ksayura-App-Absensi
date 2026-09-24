<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    recap: { type: Object, required: true },
    links: { type: Object, required: true },
});

const toInt = (value) => Math.max(0, Number(value || 0));
const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(toInt(value));

const sortedExpenses = props.recap.expenses;

const deleteForm = useForm({
    confirmation_text: '',
    recap_date: '',
    employee_id: '',
    password: '',
});

const destroyRecap = () => {
    deleteForm.delete(props.links.destroy, {
        preserveScroll: true,
        onSuccess: () => deleteForm.reset(),
    });
};
</script>

<template>
    <Head title="Detail Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-6xl space-y-5">
            <section class="rounded-lg bg-slate-950 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Detail Rekap Harian</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black">{{ recap.employee?.name || '-' }}</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ recap.recap_date_label }} - {{ recap.employee?.employee_code || '-' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="links.index" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/15">Kembali</Link>
                        <Link :href="links.edit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600">Edit</Link>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-4">
                <Card><p class="text-xs font-bold text-slate-400">Pengeluaran</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_expense_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">QRIS</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_qris_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">Sisa</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.remaining_cash_amount) }}</p></Card>
                <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm"><p class="text-xs font-bold text-emerald-100">Modal</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.capital_amount) }}</p></section>
            </section>

            <div class="grid gap-5 lg:grid-cols-2">
                <Card>
                    <h2 class="text-lg font-black">Pengeluaran Terpisah</h2>
                    <div class="mt-4 divide-y divide-slate-100">
                        <div v-for="expense in sortedExpenses" :key="expense.id" class="flex justify-between gap-4 py-3 text-sm">
                            <span class="font-semibold text-slate-700">
                                <span class="block">{{ expense.name }}</span>
                            </span>
                            <b>{{ formatRupiah(expense.amount) }}</b>
                        </div>
                        <p v-if="recap.expenses.length === 0" class="py-8 text-center text-sm text-slate-400">Tidak ada pengeluaran.</p>
                    </div>
                </Card>

                <Card>
                    <div>
                        <h2 class="text-lg font-black">QRIS</h2>
                        <p class="mt-1 text-xs text-slate-500">Owner hanya dapat membuka foto bukti pada hari rekap tersebut. Nominal tetap tersimpan permanen.</p>
                    </div>
                    <div class="mt-4 space-y-3">
                        <article v-for="transaction in recap.qris_transactions" :key="transaction.id" class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black">{{ formatRupiah(transaction.amount) }}</p>
                                    <p v-if="transaction.employee" class="mt-1 text-xs text-slate-500">{{ transaction.employee.name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ transaction.evidence_deleted_at ? `Foto terhapus ${transaction.evidence_deleted_at}` : `Bukti sampai ${transaction.evidence_expires_at || '-'}` }}
                                    </p>
                                </div>
                                <a v-if="transaction.evidence_url" :href="transaction.evidence_url" target="_blank" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Lihat Bukti</a>
                                <span v-else class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">Foto tidak tersedia</span>
                            </div>
                        </article>
                        <p v-if="recap.qris_transactions.length === 0" class="py-8 text-center text-sm text-slate-400">Tidak ada transaksi QRIS.</p>
                    </div>
                </Card>
            </div>

            <Card>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-red-700">Hapus Rekap Harian</h2>
                        <p class="mt-1 text-sm text-slate-500">Tindakan ini menghapus rekap, bukaan pengeluaran, item pengeluaran, transaksi QRIS, dan file bukti QRIS yang tersimpan.</p>
                    </div>
                    <span class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">Owner only</span>
                </div>

                <form class="mt-5 grid gap-4 lg:grid-cols-2" @submit.prevent="destroyRecap">
                    <label class="block">
                        <span class="text-xs font-bold text-slate-500">Ketik HAPUS REKAP</span>
                        <input
                            v-model="deleteForm.confirmation_text"
                            type="text"
                            class="mt-1 w-full rounded-lg border-slate-200 text-sm font-bold shadow-sm focus:border-red-500 focus:ring-red-100"
                            autocomplete="off"
                        >
                        <span v-if="deleteForm.errors.confirmation_text" class="mt-2 block text-xs font-semibold text-red-600">{{ deleteForm.errors.confirmation_text }}</span>
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-500">Tanggal rekap</span>
                        <input
                            v-model="deleteForm.recap_date"
                            type="date"
                            class="mt-1 w-full rounded-lg border-slate-200 text-sm font-bold shadow-sm focus:border-red-500 focus:ring-red-100"
                        >
                        <span v-if="deleteForm.errors.recap_date" class="mt-2 block text-xs font-semibold text-red-600">{{ deleteForm.errors.recap_date }}</span>
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-500">ID karyawan: {{ recap.employee?.id || '-' }}</span>
                        <input
                            v-model="deleteForm.employee_id"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-lg border-slate-200 text-sm font-bold shadow-sm focus:border-red-500 focus:ring-red-100"
                            autocomplete="off"
                        >
                        <span v-if="deleteForm.errors.employee_id" class="mt-2 block text-xs font-semibold text-red-600">{{ deleteForm.errors.employee_id }}</span>
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-500">Password owner</span>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            class="mt-1 w-full rounded-lg border-slate-200 text-sm font-bold shadow-sm focus:border-red-500 focus:ring-red-100"
                            autocomplete="current-password"
                        >
                        <span v-if="deleteForm.errors.password" class="mt-2 block text-xs font-semibold text-red-600">{{ deleteForm.errors.password }}</span>
                    </label>

                    <div class="lg:col-span-2">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-red-600 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="deleteForm.processing"
                        >
                            {{ deleteForm.processing ? 'Menghapus...' : 'Hapus Rekap Harian' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
