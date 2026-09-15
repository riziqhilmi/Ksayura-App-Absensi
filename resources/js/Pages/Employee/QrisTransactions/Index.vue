<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    employee: { type: Object, required: true },
    transactions: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    filters: { type: Object, required: true },
    links: { type: Object, required: true },
});

const form = useForm({
    transaction_date: props.filters.date,
    amount: '',
    evidence: null,
});

const cameraInput = ref(null);
const uploadInput = ref(null);
const evidenceSource = ref('');

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

const hasTransactions = computed(() => props.transactions.length > 0);
const canEdit = computed(() => props.summary.is_open);

const changeDate = () => {
    router.get(props.links.index, { date: form.transaction_date }, {
        preserveState: false,
        preserveScroll: true,
    });
};

const evidenceLabel = computed(() => form.evidence?.name || '');

const setEvidence = (event, source) => {
    form.evidence = event.target.files?.[0] || null;
    evidenceSource.value = form.evidence ? source : '';
};

const clearEvidence = () => {
    form.evidence = null;
    evidenceSource.value = '';

    if (cameraInput.value) {
        cameraInput.value.value = '';
    }

    if (uploadInput.value) {
        uploadInput.value.value = '';
    }
};

const submit = () => {
    if (!canEdit.value) return;

    form
        .transform((data) => ({
            transaction_date: data.transaction_date,
            amount: toInt(data.amount),
            evidence: data.evidence,
        }))
        .post(props.links.store, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.amount = '';
                clearEvidence();
            },
        });
};

const removeTransaction = (transaction) => {
    if (!window.confirm('Hapus transaksi QRIS ini?')) {
        return;
    }

    router.delete(transaction.urls.destroy, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="QRIS" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="rounded-lg bg-slate-950 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">QRIS Karyawan</p>
                <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black">{{ employee.name }}</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ employee.employee_code }} - transaksi pelanggan sepanjang hari</p>
                    </div>
                    <label class="block sm:w-56">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal</span>
                        <input
                            v-model="form.transaction_date"
                            type="date"
                            :max="filters.today"
                            class="mt-1 w-full rounded-lg border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-white shadow-sm focus:border-emerald-300 focus:ring-emerald-300"
                            @change="changeDate"
                        >
                    </label>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
                <Card>
                    <h2 class="text-lg font-black text-slate-900">Tambah QRIS</h2>
                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <p v-if="!canEdit" class="rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">
                            Buka pengeluaran terlebih dahulu sebelum menambah QRIS.
                        </p>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-500">Nominal</span>
                            <input
                                :value="nominalInputValue(form.amount)"
                                type="text"
                                inputmode="numeric"
                                class="mt-1 w-full rounded-lg border-slate-200 text-lg font-black shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="0"
                                :disabled="!canEdit"
                                @input="setNominal"
                            >
                            <span v-if="form.errors.amount" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.amount }}</span>
                        </label>

                        <label class="block rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3">
                            <span class="text-xs font-bold text-slate-500">Foto bukti</span>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-center text-sm font-bold text-white hover:bg-emerald-700" :class="{ 'cursor-not-allowed opacity-60': !canEdit }">
                                    Kamera Langsung
                                    <input
                                        ref="cameraInput"
                                        type="file"
                                        accept="image/*"
                                        capture="environment"
                                        class="sr-only"
                                        :disabled="!canEdit"
                                        @change="setEvidence($event, 'kamera')"
                                    >
                                </label>
                                <label class="flex cursor-pointer items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-center text-sm font-bold text-white hover:bg-slate-800" :class="{ 'cursor-not-allowed opacity-60': !canEdit }">
                                    Upload File
                                    <input
                                        ref="uploadInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        :disabled="!canEdit"
                                        @change="setEvidence($event, 'upload')"
                                    >
                                </label>
                            </div>
                            <div v-if="evidenceLabel" class="mt-3 flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 text-xs text-slate-600">
                                <span class="min-w-0 truncate">
                                    {{ evidenceSource === 'kamera' ? 'Foto kamera' : 'File upload' }}: <b>{{ evidenceLabel }}</b>
                                </span>
                                <button type="button" class="shrink-0 font-bold text-red-600" @click="clearEvidence">
                                    Hapus
                                </button>
                            </div>
                            <span v-if="form.errors.evidence" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.evidence }}</span>
                        </label>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-black text-white hover:bg-emerald-700 disabled:opacity-60"
                            :disabled="form.processing || !canEdit"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan QRIS' }}
                        </button>
                    </form>
                </Card>

                <div class="space-y-5">
                    <section class="rounded-lg bg-emerald-600 p-5 text-white shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Total QRIS Rekap Ini</p>
                        <p class="mt-2 text-3xl font-black">{{ formatRupiah(summary.total_qris_amount) }}</p>
                        <p class="mt-1 text-sm text-emerald-100">{{ summary.count }} transaksi</p>
                    </section>

                    <Card>
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-slate-900">Daftar Transaksi</h2>
                            <Link :href="links.daily_recap" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200">
                                Ke Rekap
                            </Link>
                        </div>

                        <div class="mt-4 space-y-3">
                            <article v-for="transaction in transactions" :key="transaction.id" class="rounded-lg border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-black text-slate-900">{{ formatRupiah(transaction.amount) }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ transaction.created_at }}</p>
                                        <p v-if="transaction.evidence_deleted_at" class="mt-1 text-xs text-slate-500">Foto terhapus {{ transaction.evidence_deleted_at }}</p>
                                    </div>
                                    <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600" @click="removeTransaction(transaction)">
                                        Hapus
                                    </button>
                                </div>
                                <a v-if="transaction.evidence_url" :href="transaction.evidence_url" target="_blank" class="mt-3 inline-block text-sm font-bold text-emerald-700">
                                    Lihat bukti
                                </a>
                                <p v-else class="mt-3 text-xs font-semibold text-slate-400">Foto tidak tersedia</p>
                            </article>

                            <div v-if="!hasTransactions" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                                Belum ada transaksi QRIS pada rekap ini.
                            </div>
                        </div>
                    </Card>
                </div>
            </section>
        </div>
    </AppShell>
</template>
