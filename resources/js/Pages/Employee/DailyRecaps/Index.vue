<script setup>
import { computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    employee: {
        type: Object,
        required: true,
    },
    recap: {
        type: Object,
        default: null,
    },
    expenseSession: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    links: {
        type: Object,
        required: true,
    },
});

const buildInitialForm = () => ({
    recap_date: props.recap?.recap_date || props.filters.date,
    capital_amount: props.recap?.capital_amount || 0,
    remaining_cash_amount: props.recap?.remaining_cash_amount || 0,
});

const form = useForm(buildInitialForm());

watch(
    () => [props.recap, props.filters.date],
    () => {
        form.defaults(buildInitialForm());
        form.reset();
        form.clearErrors();
    }
);

const toInt = (value) => {
    const number = Number(value || 0);
    return Number.isFinite(number) && number > 0 ? Math.floor(number) : 0;
};

const formatRupiah = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(toInt(value));

const totalExpenses = computed(() =>
    toInt(props.recap?.total_expense_amount || 0)
);

const totalQris = computed(() => toInt(props.recap?.total_qris_amount || 0));

const sisaPlusQris = computed(() =>
    toInt(form.remaining_cash_amount) + totalQris.value
);

const filledExpenses = computed(() =>
    props.recap?.expenses || []
);

const sortedFilledExpenses = computed(() =>
    [...filledExpenses.value]
);

const canSaveRecap = computed(() =>
    props.expenseSession.is_open && !props.expenseSession.is_closed
);

const expenseSessionLabel = computed(() => {
    if (props.expenseSession.is_closed) return 'Rekap sebelumnya sudah ditutup';
    if (props.expenseSession.is_open) return 'Bukaan rekap sedang aktif';
    return 'Belum ada bukaan rekap';
});

const parseRupiahInput = (value) =>
    toInt(String(value ?? '').replace(/\D/g, ''));

const rupiahInputValue = (value) => {
    const amount = toInt(value);

    return amount > 0 ? amount.toLocaleString('id-ID') : '';
};

const setFormAmount = (field, event) => {
    form[field] = parseRupiahInput(event.target.value);
};

const changeDate = () => {
    router.get(
        props.links.form,
        { date: form.recap_date },
        {
            preserveScroll: true,
            preserveState: false,
        }
    );
};

const submit = () => {
    if (!canSaveRecap.value) return;

    form
        .transform((data) => ({
            recap_date: data.recap_date,
            capital_amount: toInt(data.capital_amount),
            remaining_cash_amount: toInt(data.remaining_cash_amount),
        }))
        .post(props.links.store, {
            forceFormData: true,
            preserveScroll: true,
        });
};

</script>

<template>
    <Head title="Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="overflow-hidden rounded-lg bg-slate-950 text-white shadow-sm">
                <div class="px-5 py-6 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Rekap Harian Karyawan</p>
                    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-black tracking-normal sm:text-3xl">{{ employee.name }}</h1>
                            <p class="mt-1 text-sm font-medium text-slate-300">
                                {{ employee.position || 'Karyawan' }} - {{ employee.employee_code }}
                            </p>
                        </div>

                        <label class="block sm:w-56">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal</span>
                            <input
                                v-model="form.recap_date"
                                type="date"
                                :max="filters.today"
                                class="mt-1 w-full rounded-lg border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-white shadow-sm focus:border-emerald-300 focus:ring-emerald-300"
                                @change="changeDate"
                            >
                        </label>
                    </div>
                </div>
            </section>

            <form class="space-y-5" @submit.prevent="submit">
                <Card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Status Bukaan</p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">{{ expenseSessionLabel }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                <span v-if="expenseSession.opened_at">Dibuka {{ expenseSession.opened_at }}.</span>
                                <span v-if="expenseSession.opened_by"> Oleh {{ expenseSession.opened_by.name }}.</span>
                                <span v-if="expenseSession.closed_at"> Ditutup {{ expenseSession.closed_at }}.</span>
                                <span v-if="expenseSession.closed_by"> Oleh {{ expenseSession.closed_by.name }}.</span>
                                <span v-if="!expenseSession.opened_at">Buka pengeluaran terlebih dahulu untuk mulai rekap.</span>
                            </p>
                        </div>
                        <Link
                            :href="links.expenses"
                            class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800"
                        >
                            {{ expenseSession.is_closed ? 'Buka Rekap Baru' : 'Kelola Bukaan' }}
                        </Link>
                    </div>

                    <p v-if="!canSaveRecap" class="mt-4 rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">
                        Rekap baru bisa disimpan setelah ada bukaan baru yang aktif.
                    </p>
                </Card>

                <section class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Modal Shift</p>
                        <p class="mt-2 text-2xl font-black text-emerald-700">{{ formatRupiah(form.capital_amount) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Tersinkron semua karyawan.</p>
                    </Card>

                    <Card>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Pengeluaran</p>
                        <p class="mt-2 text-2xl font-black text-slate-950">{{ formatRupiah(totalExpenses) }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ filledExpenses.length }} transaksi terpisah</p>
                    </Card>

                    <Card>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Sisa Tunai</p>
                        <p class="mt-2 text-2xl font-black text-emerald-700">{{ formatRupiah(form.remaining_cash_amount) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Tersinkron semua karyawan.</p>
                    </Card>

                    <Card>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">QRIS</p>
                        <p class="mt-2 text-2xl font-black text-blue-700">{{ formatRupiah(totalQris) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Dari halaman transaksi QRIS.</p>
                    </Card>
                </section>

                <Card>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold text-slate-500">Modal shift</span>
                            <input
                                :value="rupiahInputValue(form.capital_amount)"
                                type="text"
                                inputmode="numeric"
                                class="mt-1 w-full rounded-lg border-slate-200 text-lg font-black shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="0"
                                :disabled="!canSaveRecap"
                                @input="setFormAmount('capital_amount', $event)"
                            >
                            <span v-if="form.errors.capital_amount" class="mt-2 block text-xs font-semibold text-red-600">
                                {{ form.errors.capital_amount }}
                            </span>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-500">Uang sisa shift ini</span>
                            <input
                                :value="rupiahInputValue(form.remaining_cash_amount)"
                                type="text"
                                inputmode="numeric"
                                class="mt-1 w-full rounded-lg border-slate-200 text-lg font-black shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="0"
                                :disabled="!canSaveRecap"
                                @input="setFormAmount('remaining_cash_amount', $event)"
                            >
                            <span v-if="form.errors.remaining_cash_amount" class="mt-2 block text-xs font-semibold text-red-600">
                                {{ form.errors.remaining_cash_amount }}
                            </span>
                        </label>
                    </div>
                </Card>

                <Card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Pengeluaran Terpisah</h2>
                            <p class="mt-1 text-sm text-slate-500">Pengeluaran bersama dari semua karyawan pada tanggal ini.</p>
                        </div>

                        <Link
                            :href="links.expenses"
                            class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Kelola Pengeluaran
                        </Link>
                    </div>

                    <div class="mt-5 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        <div
                            v-for="(expense, index) in sortedFilledExpenses"
                            :key="`summary-expense-${index}-${expense.id || 'new'}`"
                            class="flex items-center justify-between gap-3 px-4 py-3"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">{{ expense.name || `Item ${index + 1}` }}</p>
                                <p class="text-xs text-slate-400">{{ expense.created_by_name || expense.session_opened_by ? `oleh ${expense.created_by_name || expense.session_opened_by}` : 'Pengeluaran bersama' }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-slate-900">{{ formatRupiah(expense.amount) }}</p>
                        </div>

                        <div v-if="filledExpenses.length === 0" class="px-4 py-8 text-center text-sm text-slate-400">
                            Belum ada pengeluaran barang.
                        </div>
                    </div>
                </Card>

                <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Sisa + QRIS</p>
                            <p class="mt-2 text-3xl font-black">{{ formatRupiah(sisaPlusQris) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-emerald-50">Hitungan otomatis dari sisa tunai dan QRIS.</p>
                    </div>

                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-emerald-100">Modal Shift</p>
                            <b>{{ formatRupiah(form.capital_amount) }}</b>
                        </div>
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-emerald-100">Sisa</p>
                            <b>{{ formatRupiah(form.remaining_cash_amount) }}</b>
                        </div>
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-emerald-100">Total QRIS</p>
                            <b>{{ formatRupiah(totalQris) }}</b>
                        </div>
                    </div>

                </section>

                <Card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">QRIS Hari Ini</h2>
                            <p class="mt-1 text-sm text-slate-500">Transaksi QRIS dicatat di halaman terpisah sepanjang hari.</p>
                        </div>

                        <Link
                            :href="links.qris"
                            class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800"
                        >
                            Kelola QRIS
                        </Link>
                    </div>

                    <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total QRIS dari transaksi terpisah</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ formatRupiah(totalQris) }}</p>
                    </div>
                </Card>

                <div class="sticky bottom-20 z-10 flex flex-col gap-2 rounded-lg border border-slate-200 bg-white p-3 shadow-lg sm:flex-row lg:static lg:shadow-none">
                    <Link
                        :href="links.index"
                        class="inline-flex justify-center rounded-lg bg-slate-100 px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-200 sm:w-40"
                    >
                        Kembali
                    </Link>
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing || !canSaveRecap"
                    >
                        {{ form.processing ? 'Menyimpan...' : (recap?.id ? 'Simpan Perubahan' : 'Simpan Rekap Harian') }}
                    </button>
                </div>
            </form>
        </div>
    </AppShell>
</template>
