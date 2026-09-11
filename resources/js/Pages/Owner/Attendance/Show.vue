<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    attendance: { type: Object, required: true },
    links: { type: Object, required: true },
});

const showModal = ref(false);
const processing = ref(false);
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const form = reactive({
    status: props.attendance.status,
    notes: props.attendance.notes || '',
});

const statusLabels = {
    present: 'Hadir',
    late: 'Terlambat',
    absent: 'Tidak Hadir',
    half_day: 'Setengah Hari',
    leave: 'Cuti',
    auto_checkout: 'Auto Check Out',
};

const badgeClass = (status) => ({
    present: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    late: 'bg-amber-50 text-amber-700 ring-amber-200',
    absent: 'bg-red-50 text-red-700 ring-red-200',
    half_day: 'bg-blue-50 text-blue-700 ring-blue-200',
    leave: 'bg-violet-50 text-violet-700 ring-violet-200',
    auto_checkout: 'bg-orange-50 text-orange-700 ring-orange-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');

const submit = () => {
    processing.value = true;
    fetch(props.links.updateStatus, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: JSON.stringify(form),
    }).then((response) => {
        if (response.ok) {
            showModal.value = false;
            router.reload({ preserveScroll: true });
        }
    }).finally(() => {
        processing.value = false;
    });
};

const openLocation = (lat, lng) => {
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
};
</script>

<template>
    <Head title="Detail Absensi" />

    <AppShell>
        <div class="mx-auto max-w-5xl space-y-6">
            <section class="rounded-2xl bg-gradient-to-r from-blue-600 to-emerald-500 p-6 text-white shadow-lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-50">Detail Absensi</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ attendance.date_long }}</h1>
                        <p class="mt-1 text-sm text-blue-50">{{ attendance.employee?.name || '-' }}</p>
                    </div>
                    <span class="w-fit rounded-full px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(attendance.status)">
                        {{ statusLabels[attendance.status] || attendance.status }}
                    </span>
                </div>
            </section>

            <Card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-xl font-bold text-white">
                        {{ attendance.employee?.name?.charAt(0) || 'K' }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-slate-900">{{ attendance.employee?.name || '-' }}</h2>
                        <p class="text-sm text-slate-500">{{ attendance.employee?.employee_code || '-' }} | {{ attendance.employee?.position || 'Staff' }}</p>
                        <p class="text-sm text-slate-500">{{ attendance.employee?.email || '-' }}</p>
                    </div>
                    <div class="sm:ml-auto sm:text-right">
                        <p class="text-sm text-slate-500">Shift</p>
                        <p class="font-bold">{{ attendance.shift?.name || '-' }}</p>
                        <p v-if="attendance.shift" class="text-xs text-slate-500">{{ attendance.shift.start_time }} - {{ attendance.shift.end_time }}</p>
                    </div>
                </div>
            </Card>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Card>
                    <h2 class="text-base font-bold">Timeline Absensi</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-lg bg-emerald-50 p-4">
                            <p class="text-sm font-semibold text-emerald-700">Check In</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ attendance.check_in_time || '-' }}</p>
                            <p class="text-sm text-slate-500">{{ attendance.check_in_date || 'Belum check in' }}</p>
                            <button v-if="attendance.latitude_in && attendance.longitude_in" type="button" class="mt-3 text-sm font-bold text-blue-700" @click="openLocation(attendance.latitude_in, attendance.longitude_in)">Lihat Lokasi</button>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-4">
                            <p class="text-sm font-semibold text-blue-700">Check Out</p>
                            <p class="mt-1 text-2xl font-bold text-blue-700">{{ attendance.check_out_time || '-' }}</p>
                            <p class="text-sm text-slate-500">{{ attendance.check_out_date || 'Belum check out' }}</p>
                            <button v-if="attendance.latitude_out && attendance.longitude_out" type="button" class="mt-3 text-sm font-bold text-blue-700" @click="openLocation(attendance.latitude_out, attendance.longitude_out)">Lihat Lokasi</button>
                        </div>
                        <div v-if="attendance.work_duration_text" class="rounded-lg bg-violet-50 p-4">
                            <p class="text-sm font-semibold text-violet-700">Durasi Kerja</p>
                            <p class="mt-1 text-2xl font-bold text-violet-700">{{ attendance.work_duration_text }}</p>
                        </div>
                    </div>
                </Card>

                <Card>
                    <h2 class="text-base font-bold">Status & Catatan</h2>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                            <dt class="text-slate-500">Status</dt>
                            <dd><span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="badgeClass(attendance.status)">{{ statusLabels[attendance.status] || attendance.status }}</span></dd>
                        </div>
                        <div v-if="attendance.late_minutes > 0" class="flex justify-between border-b border-slate-100 pb-3">
                            <dt class="text-slate-500">Keterlambatan</dt>
                            <dd class="font-bold text-amber-600">{{ attendance.late_text }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <dt class="text-slate-500">Akurasi GPS</dt>
                            <dd class="text-right font-semibold">In: {{ attendance.gps_accuracy_in || '-' }}m / Out: {{ attendance.gps_accuracy_out || '-' }}m</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <dt class="text-slate-500">Dibuat</dt>
                            <dd class="font-semibold">{{ attendance.created_at }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Catatan</dt>
                            <dd class="mt-2 rounded-lg bg-slate-50 p-3 text-slate-700">{{ attendance.notes || '-' }}</dd>
                        </div>
                    </dl>
                </Card>
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</Link>
                <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" @click="showModal = true">Edit Status</button>
            </div>
        </div>

        <Modal :show="showModal" title="Edit Status Absensi" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Status</label>
                    <select v-model="form.status" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-100">
                        <option value="present">Hadir</option>
                        <option value="late">Terlambat</option>
                        <option value="half_day">Setengah Hari</option>
                        <option value="absent">Tidak Hadir</option>
                        <option value="leave">Cuti</option>
                        <option value="auto_checkout">Auto Check Out</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Catatan</label>
                    <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-100" />
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700" @click="showModal = false">Batal</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AppShell>
</template>
