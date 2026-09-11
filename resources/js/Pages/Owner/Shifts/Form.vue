<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import FormField from '../../../Components/FormField.vue';

const props = defineProps({
    mode: { type: String, required: true },
    shift: { type: Object, default: null },
    links: { type: Object, required: true },
});

const isEdit = computed(() => props.mode === 'edit');

const form = useForm({
    name: props.shift?.name ?? '',
    start_time: props.shift?.start_time ?? '08:00',
    end_time: props.shift?.end_time ?? '17:00',
    break_start: props.shift?.break_start ?? '',
    break_end: props.shift?.break_end ?? '',
    grace_period: props.shift?.grace_period ?? 15,
    status: props.shift?.status ?? 'active',
    notes: props.shift?.notes ?? '',
});

const submit = () => {
    if (isEdit.value) {
        form.put(props.links.submit, { preserveScroll: true });
        return;
    }

    form.post(props.links.submit, { preserveScroll: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Shift' : 'Tambah Shift'" />

    <AppShell>
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Master Shift</p>
                <h1 class="text-2xl font-bold text-slate-950">{{ isEdit ? 'Edit Shift' : 'Tambah Shift Baru' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Lengkapi jam kerja, waktu istirahat, toleransi keterlambatan, dan status shift.</p>
            </div>

            <Card>
                <form class="space-y-7" @submit.prevent="submit">
                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Informasi Dasar</h2>
                        <FormField label="Nama Shift *" :error="form.errors.name">
                            <input v-model="form.name" type="text" required placeholder="Contoh: Shift Pagi, Shift Siang, Shift Malam" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        </FormField>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Waktu Shift</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField label="Jam Mulai *" :error="form.errors.start_time">
                                <input v-model="form.start_time" type="time" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Jam Selesai *" :error="form.errors.end_time">
                                <input v-model="form.end_time" type="time" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Waktu Istirahat</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField label="Mulai Istirahat" :error="form.errors.break_start">
                                <input v-model="form.break_start" type="time" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                            <FormField label="Selesai Istirahat" :error="form.errors.break_end">
                                <input v-model="form.break_end" type="time" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            </FormField>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900">Pengaturan</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField label="Toleransi Keterlambatan (menit) *" :error="form.errors.grace_period">
                                <input v-model="form.grace_period" type="number" required min="0" max="60" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                <span class="mt-1 block text-xs text-slate-500">Jumlah menit toleransi setelah jam mulai shift (0-60 menit)</span>
                            </FormField>
                            <FormField v-if="isEdit" label="Status *" :error="form.errors.status">
                                <select v-model="form.status" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </FormField>
                        </div>
                    </section>

                    <FormField label="Catatan" :error="form.errors.notes">
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                    </FormField>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <Link :href="links.index" class="inline-flex justify-center rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </Link>
                        <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Update Shift' : 'Simpan Shift') }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AppShell>
</template>
