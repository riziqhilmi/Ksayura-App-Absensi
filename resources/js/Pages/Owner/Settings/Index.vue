<script setup>
import { Head, useForm } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import FormField from '../../../Components/FormField.vue';

const props = defineProps({
    office: { type: Object, required: true },
    market: { type: Object, required: true },
    locations: { type: Array, default: () => [] },
    links: { type: Object, required: true },
});

const form = useForm({
    latitude: props.office.latitude ?? '',
    longitude: props.office.longitude ?? '',
    radius: props.office.radius ?? 100,
    address: props.office.address ?? '',
    market_latitude: props.market.latitude ?? '',
    market_longitude: props.market.longitude ?? '',
    market_radius: props.market.radius ?? props.office.radius ?? 100,
    market_address: props.market.address ?? '',
});

const submit = () => {
    form.post(props.links.updateOffice, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengaturan" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-bold text-slate-950">Pengaturan Sistem</h1>
                <p class="mt-1 text-sm text-slate-500">Atur titik lokasi yang dipakai untuk validasi absensi karyawan.</p>
            </div>

            <Card>
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Lokasi Kantor</h2>
                        <p class="mt-1 text-sm text-slate-500">Karyawan dapat absen jika berada dalam radius lokasi kantor.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField label="Latitude *" :error="form.errors.latitude">
                            <input v-model="form.latitude" type="text" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <span class="mt-1 block text-xs text-slate-400">Contoh: -8.180305</span>
                        </FormField>

                        <FormField label="Longitude *" :error="form.errors.longitude">
                            <input v-model="form.longitude" type="text" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                            <span class="mt-1 block text-xs text-slate-400">Contoh: 113.725896</span>
                        </FormField>
                    </div>

                    <FormField label="Radius Absensi Kantor (meter) *" :error="form.errors.radius">
                        <input v-model="form.radius" type="number" min="10" max="5000" required class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        <span class="mt-1 block text-xs text-slate-400">Jarak maksimum dari titik kantor, 10 sampai 5000 meter.</span>
                    </FormField>

                    <FormField label="Alamat Kantor" :error="form.errors.address">
                        <textarea v-model="form.address" rows="3" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                    </FormField>

                    <div class="border-t border-slate-100 pt-6">
                        <h2 class="text-lg font-black text-slate-900">Lokasi Pasar</h2>
                        <p class="mt-1 text-sm text-slate-500">Opsional. Jika diisi, absensi juga diterima dari titik pasar.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField label="Latitude Pasar" :error="form.errors.market_latitude">
                            <input v-model="form.market_latitude" type="text" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        </FormField>

                        <FormField label="Longitude Pasar" :error="form.errors.market_longitude">
                            <input v-model="form.market_longitude" type="text" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        </FormField>
                    </div>

                    <FormField label="Radius Absensi Pasar (meter)" :error="form.errors.market_radius">
                        <input v-model="form.market_radius" type="number" min="10" max="5000" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                    </FormField>

                    <FormField label="Alamat Pasar" :error="form.errors.market_address">
                        <textarea v-model="form.market_address" rows="3" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                    </FormField>

                    <div class="flex justify-end border-t border-slate-100 pt-5">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                        </button>
                    </div>
                </form>
            </Card>

            <Card>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Lokasi Absensi Aktif</h2>
                    <p class="mt-1 text-sm text-slate-500">Absensi valid jika karyawan berada dalam radius salah satu lokasi aktif.</p>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <article v-for="location in locations" :key="location.key" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-900">{{ location.name }}</p>
                        <p class="mt-2 text-lg font-black text-slate-950">{{ location.latitude }}, {{ location.longitude }}</p>
                        <p class="mt-1 text-sm text-slate-600">Radius {{ location.radius }} meter</p>
                        <p class="mt-1 text-sm text-slate-500">{{ location.address }}</p>
                    </article>

                    <div v-if="locations.length === 0" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400 md:col-span-2">
                        Belum ada lokasi absensi aktif.
                    </div>
                </div>
            </Card>
        </div>
    </AppShell>
</template>
