<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    recap: { type: Object, required: true },
    links: { type: Object, required: true },
});

const toInt = (value) => {
    const number = Number(value || 0);
    return Number.isFinite(number) && number > 0 ? Math.floor(number) : 0;
};

const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(toInt(value));

const parseRupiahInput = (value) => toInt(String(value ?? '').replace(/\D/g, ''));
const rupiahInputValue = (value) => {
    const amount = toInt(value);

    return amount > 0 ? amount.toLocaleString('id-ID') : '';
};

const buildInitialForm = () => ({
    capital_amount: props.recap.capital_amount || 0,
    remaining_cash_amount: props.recap.remaining_cash_amount || 0,
});

const form = useForm(buildInitialForm());

watch(
    () => props.recap,
    () => {
        form.defaults(buildInitialForm());
        form.reset();
        form.clearErrors();
    }
);

const setFormAmount = (field, event) => {
    form[field] = parseRupiahInput(event.target.value);
};

const sisaPlusQris = computed(() =>
    toInt(form.remaining_cash_amount) + toInt(props.recap.total_qris_amount)
);

const submit = () => {
    form
        .transform((data) => ({
            capital_amount: toInt(data.capital_amount),
            remaining_cash_amount: toInt(data.remaining_cash_amount),
        }))
        .put(props.links.update, {
            preserveScroll: true,
        });
};
</script>

<template>
    <Head title="Edit Rekap Harian" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="rounded-lg bg-slate-950 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Edit Rekap Harian</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black">{{ recap.employee?.name || '-' }}</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ recap.recap_date_label }} - {{ recap.employee?.employee_code || '-' }}</p>
                    </div>
                    <Link :href="links.show" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/15">Kembali</Link>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-4">
                <Card><p class="text-xs font-bold text-slate-400">Pengeluaran</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_expense_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">QRIS</p><p class="mt-2 text-xl font-black">{{ formatRupiah(recap.total_qris_amount) }}</p></Card>
                <Card><p class="text-xs font-bold text-slate-400">Sisa Baru</p><p class="mt-2 text-xl font-black text-emerald-700">{{ formatRupiah(form.remaining_cash_amount) }}</p></Card>
                <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm"><p class="text-xs font-bold text-emerald-100">Modal Baru</p><p class="mt-2 text-xl font-black">{{ formatRupiah(form.capital_amount) }}</p></section>
            </section>

            <form class="space-y-5" @submit.prevent="submit">
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
                                @input="setFormAmount('remaining_cash_amount', $event)"
                            >
                            <span v-if="form.errors.remaining_cash_amount" class="mt-2 block text-xs font-semibold text-red-600">
                                {{ form.errors.remaining_cash_amount }}
                            </span>
                        </label>
                    </div>
                </Card>

                <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Sisa + QRIS</p>
                            <p class="mt-2 text-3xl font-black">{{ formatRupiah(sisaPlusQris) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-emerald-50">Total pengeluaran dan QRIS disinkronkan ulang saat disimpan.</p>
                    </div>
                </section>

                <div class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-white p-3 shadow-sm sm:flex-row">
                    <Link
                        :href="links.show"
                        class="inline-flex justify-center rounded-lg bg-slate-100 px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-200 sm:w-40"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </AppShell>
</template>
