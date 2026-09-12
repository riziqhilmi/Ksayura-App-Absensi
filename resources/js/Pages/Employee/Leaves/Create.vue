<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';

const props = defineProps({
    pendingCount: { type: Number, default: 0 },
    options: { type: Object, required: true },
    links: { type: Object, required: true },
    defaults: { type: Object, required: true },
});

const availability = ref(null);
const checking = ref(false);

const form = useForm({
    leave_type: 'annual',
    start_date: props.defaults.start_date,
    end_date: props.defaults.end_date,
    reason: '',
});

const durationDays = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(`${form.start_date}T00:00:00`);
    const end = new Date(`${form.end_date}T00:00:00`);
    const diff = Math.floor((end - start) / 86400000) + 1;
    return diff > 0 ? diff : 0;
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const checkAvailability = async () => {
    if (!form.start_date || !form.end_date || durationDays.value <= 0) {
        availability.value = null;
        return;
    }

    checking.value = true;
    const response = await fetch(props.links.checkAvailability, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
        },
        body: JSON.stringify({
            start_date: form.start_date,
            end_date: form.end_date,
        }),
    });

    availability.value = response.ok ? await response.json() : null;
    checking.value = false;
};

watch(() => [form.start_date, form.end_date], checkAvailability, { immediate: true });

const submit = () => {
    form.post(props.links.store, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Ajukan Cuti" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Karyawan</p>
                    <h1 class="text-2xl font-bold text-slate-950">Ajukan Cuti</h1>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi tanggal, jenis cuti, dan alasan pengajuan.</p>
                </div>
                <Link :href="links.index" class="inline-flex w-fit rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</Link>
            </div>

            <Card v-if="pendingCount > 0">
                <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-100">
                    Anda memiliki {{ pendingCount }} pengajuan yang masih menunggu persetujuan.
                </div>
            </Card>

            <Card>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Jenis Cuti</label>
                            <select v-model="form.leave_type" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <option v-for="type in options.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <p v-if="form.errors.leave_type" class="mt-1 text-xs text-red-600">{{ form.errors.leave_type }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-400">Durasi</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ durationDays }} hari</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                            <input v-model="form.start_date" type="date" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                            <input v-model="form.end_date" type="date" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <div v-if="availability" class="rounded-lg p-4 text-sm ring-1" :class="availability.available ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-700 ring-red-100'">
                        <span v-if="availability.available">Tanggal tersedia untuk diajukan.</span>
                        <span v-else-if="availability.has_conflict">Ada pengajuan cuti lain pada rentang tanggal ini.</span>
                        <span v-else-if="availability.is_past">Tanggal mulai tidak boleh di masa lalu.</span>
                        <span v-else>Tanggal belum dapat diajukan.</span>
                    </div>
                    <div v-else-if="checking" class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500 ring-1 ring-slate-100">
                        Mengecek ketersediaan tanggal...
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Alasan</label>
                        <textarea v-model="form.reason" rows="5" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" placeholder="Tuliskan alasan cuti..." />
                        <p v-if="form.errors.reason" class="mt-1 text-xs text-red-600">{{ form.errors.reason }}</p>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-5">
                        <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Batal</Link>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="form.processing || availability?.available === false">
                            {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
