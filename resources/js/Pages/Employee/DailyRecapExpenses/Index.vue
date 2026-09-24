<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    employee: { type: Object, required: true },
    expenses: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    filters: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = useForm({
    expense_date: props.filters.date,
    name: '',
    amount: '',
});

const toInt = (value) => Math.max(0, Number(value || 0));
const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(toInt(value));

const parseNominalInput = (value) =>
    toInt(String(value ?? '').replace(/\D/g, ''));

const nominalInputValue = (value) => {
    const amount = toInt(value);

    return amount > 0 ? amount.toLocaleString('id-ID') : '';
};

const setNominal = (event) => {
    form.amount = parseNominalInput(event.target.value);
};

const sortedExpenses = computed(() =>
    [...props.expenses]
);

const hasExpenses = computed(() => props.expenses.length > 0);
const canEdit = computed(() => props.summary.is_open && !props.summary.is_closed);
const sessionLabel = computed(() => {
    if (props.summary.is_closed) return 'Ditutup';
    if (props.summary.is_open) return 'Dibuka';
    return 'Belum dibuka';
});

const changeDate = () => {
    router.get(props.links.index, { date: form.expense_date }, {
        preserveState: false,
        preserveScroll: true,
    });
};

const submit = () => {
    form
        .transform((data) => ({
            expense_date: data.expense_date,
            name: data.name,
            amount: toInt(data.amount),
        }))
        .post(props.links.store, {
            preserveScroll: true,
            onSuccess: () => {
                form.name = '';
                form.amount = '';
            },
        });
};

const openSession = () => {
    router.post(props.links.open, { expense_date: form.expense_date }, {
        preserveScroll: true,
    });
};

const closeSession = () => {
    if (!window.confirm('Tutup pengeluaran shift ini? Item tidak bisa ditambah atau dihapus setelah ditutup.')) {
        return;
    }

    router.post(props.links.close, { expense_date: form.expense_date }, {
        preserveScroll: true,
    });
};

const removeExpense = (expense) => {
    if (!window.confirm('Hapus pengeluaran barang ini?')) {
        return;
    }

    router.delete(expense.urls.destroy, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengeluaran Barang" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="rounded-lg bg-slate-950 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Pengeluaran Barang</p>
                <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black">{{ employee.name }}</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ employee.employee_code }} - pengeluaran per sesi shift</p>
                    </div>
                    <label class="block sm:w-56">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal</span>
                        <input
                            v-model="form.expense_date"
                            type="date"
                            :max="filters.today"
                            class="mt-1 w-full rounded-lg border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-white shadow-sm focus:border-emerald-300 focus:ring-emerald-300"
                            @change="changeDate"
                        >
                    </label>
                </div>
            </section>

            <Card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Status Pengeluaran Shift</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">{{ sessionLabel }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            <span v-if="summary.opened_at">Dibuka {{ summary.opened_at }}.</span>
                            <span v-if="summary.opened_by"> Oleh {{ summary.opened_by.name }}.</span>
                            <span v-if="summary.closed_at"> Ditutup {{ summary.closed_at }}.</span>
                            <span v-if="summary.closed_by"> Oleh {{ summary.closed_by.name }}.</span>
                            <span v-if="!summary.opened_at">Buka pengeluaran sebelum mencatat barang pada shift ini.</span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="!summary.is_open"
                            type="button"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-black text-white hover:bg-emerald-700"
                            @click="openSession"
                        >
                            {{ summary.is_closed ? 'Buka Pengeluaran Baru' : 'Buka Pengeluaran' }}
                        </button>
                        <button
                            v-if="summary.is_open"
                            type="button"
                            class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-black text-white hover:bg-slate-800"
                            @click="closeSession"
                        >
                            Tutup Pengeluaran
                        </button>
                    </div>
                </div>
            </Card>

            <section class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
                <Card>
                    <h2 class="text-lg font-black text-slate-900">Tambah Pengeluaran</h2>
                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <label class="block">
                            <span class="text-xs font-bold text-slate-500">Nama / keterangan barang</span>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-100"
                                placeholder="Contoh: Kantong plastik"
                                :disabled="!canEdit"
                            >
                            <span v-if="form.errors.name" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.name }}</span>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-500">Nominal</span>
                            <input
                                :value="nominalInputValue(form.amount)"
                                type="text"
                                inputmode="numeric"
                                class="mt-1 w-full rounded-lg border-slate-200 text-lg font-black shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-100"
                                :disabled="!canEdit"
                                placeholder="0"
                                @input="setNominal"
                            >
                            <span v-if="form.errors.amount" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.amount }}</span>
                        </label>

                        <p v-if="!canEdit" class="rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">
                            Pengeluaran harus dibuka terlebih dahulu dan belum ditutup untuk menambah item.
                        </p>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-black text-white hover:bg-emerald-700 disabled:opacity-60"
                            :disabled="form.processing || !canEdit"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengeluaran' }}
                        </button>
                    </form>
                </Card>

                <div class="space-y-5">
                    <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Total Pengeluaran Shift Ini</p>
                        <p class="mt-2 text-3xl font-black">{{ formatRupiah(summary.total_expense_amount) }}</p>
                        <p class="mt-1 text-sm text-emerald-100">{{ summary.count }} item pengeluaran</p>
                    </section>

                    <Card>
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-slate-900">Daftar Pengeluaran</h2>
                            <Link :href="links.daily_recap" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200">
                                Ke Rekap
                            </Link>
                        </div>

                        <div class="mt-4 space-y-3">
                            <article v-for="expense in sortedExpenses" :key="expense.id" class="rounded-lg border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-900">{{ expense.name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ expense.created_at }}</p>
                                        <p v-if="expense.created_by_name" class="mt-1 text-xs text-slate-500">oleh {{ expense.created_by_name }}</p>
                                        <p class="mt-2 text-lg font-black text-emerald-700">{{ formatRupiah(expense.amount) }}</p>
                                    </div>
                                    <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600 disabled:opacity-50" :disabled="!canEdit" @click="removeExpense(expense)">
                                        Hapus
                                    </button>
                                </div>
                            </article>

                            <div v-if="!hasExpenses" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                                Belum ada pengeluaran barang pada tanggal ini.
                            </div>
                        </div>
                    </Card>
                </div>
            </section>
        </div>
    </AppShell>
</template>
