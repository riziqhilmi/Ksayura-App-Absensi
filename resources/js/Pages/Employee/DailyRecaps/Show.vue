<script setup>
import { Head, Link } from '@inertiajs/vue3';

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
</script>

<template>
    <Head title="Detail Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="rounded-lg bg-slate-950 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Detail Rekap Harian</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black">{{ recap.recap_date_label }}</h1>
                        <p class="mt-1 text-sm text-slate-300">
                            Direkap {{ recap.employee?.name || '-' }}. Terakhir diperbarui {{ recap.updated_at || '-' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="links.index" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/15">Kembali</Link>
                        <Link v-if="links.edit" :href="links.edit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600">Edit</Link>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-4">
                <Card><p class="text-xs font-bold text-slate-400">Pengeluaran</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_expense_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">QRIS</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_qris_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">Sisa</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.remaining_cash_amount) }}</p></Card>
                <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm"><p class="text-xs font-bold text-emerald-100">Modal</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.capital_amount) }}</p></section>
            </section>

            <Card v-if="recap.session">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Status Bukaan</p>
                <div class="mt-2 flex flex-col gap-2 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        <b class="text-slate-900">{{ recap.session.is_open ? 'Masih aktif' : 'Ditutup' }}</b>
                        <span v-if="recap.session.opened_by"> oleh {{ recap.session.opened_by.name }}</span>
                        <span v-if="recap.session.opened_at"> pada {{ recap.session.opened_at }}</span>
                    </p>
                    <p v-if="recap.session.closed_by || recap.session.closed_at">
                        Ditutup
                        <span v-if="recap.session.closed_by"> oleh {{ recap.session.closed_by.name }}</span>
                        <span v-if="recap.session.closed_at"> pada {{ recap.session.closed_at }}</span>
                    </p>
                </div>
            </Card>

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
                    <h2 class="text-lg font-black">QRIS</h2>
                    <div class="mt-4 space-y-3">
                        <article v-for="transaction in recap.qris_transactions" :key="transaction.id" class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black">{{ formatRupiah(transaction.amount) }}</p>
                                    <p v-if="transaction.employee" class="mt-1 text-xs text-slate-500">{{ transaction.employee.name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Bukti sampai {{ transaction.evidence_expires_at || '-' }}</p>
                                </div>
                                <a v-if="transaction.evidence_url" :href="transaction.evidence_url" target="_blank" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Lihat Bukti</a>
                                <span v-else class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">Foto tidak tersedia</span>
                            </div>
                        </article>
                        <p v-if="recap.qris_transactions.length === 0" class="py-8 text-center text-sm text-slate-400">Tidak ada transaksi QRIS.</p>
                    </div>
                </Card>
            </div>
        </div>
    </AppShell>
</template>
