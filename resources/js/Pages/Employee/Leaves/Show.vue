<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';

const props = defineProps({
    leave: { type: Object, required: true },
    links: { type: Object, required: true },
});

const confirmOpen = ref(false);

const statusLabels = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' };
const statusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    rejected: 'bg-red-50 text-red-700 ring-red-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');

const cancelLeave = () => {
    confirmOpen.value = false;
    router.delete(props.leave.urls.destroy, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Detail Pengajuan Cuti" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <section class="rounded-lg bg-emerald-700 p-6 text-white shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-emerald-100">Pengajuan Cuti</p>
                        <h1 class="mt-1 text-2xl font-bold">{{ leave.leave_type_label }}</h1>
                        <p class="mt-1 text-sm text-emerald-100">{{ leave.start_date_long }} - {{ leave.end_date_long }}</p>
                    </div>
                    <span class="w-fit rounded-full px-3 py-1 text-xs font-bold ring-1" :class="statusClass(leave.status)">
                        {{ statusLabels[leave.status] || leave.status }}
                    </span>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="font-bold">Detail Cuti</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Jenis Cuti</dt><dd class="font-semibold text-right">{{ leave.leave_type_label }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Tanggal Mulai</dt><dd class="font-semibold text-right">{{ leave.start_date_long }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Tanggal Selesai</dt><dd class="font-semibold text-right">{{ leave.end_date_long }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Durasi</dt><dd class="font-semibold text-right">{{ leave.duration_days }} hari</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Diajukan</dt><dd class="font-semibold text-right">{{ leave.created_at_long }}</dd></div>
                    </dl>
                </Card>

                <Card>
                    <h2 class="font-bold">Status Pengajuan</h2>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="font-semibold text-slate-500">Alasan</p>
                            <p class="mt-1 text-slate-700">{{ leave.reason || '-' }}</p>
                        </div>
                        <div v-if="leave.status !== 'pending'" class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Diproses Oleh</span>
                            <span class="font-semibold text-right">{{ leave.approved_by || '-' }}</span>
                        </div>
                        <div v-if="leave.status !== 'pending'" class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Tanggal Diproses</span>
                            <span class="font-semibold text-right">{{ leave.approved_at || '-' }}</span>
                        </div>
                        <div v-if="leave.rejection_reason" class="rounded-lg bg-red-50 p-4 text-red-700">
                            <p class="font-semibold">Alasan Ditolak</p>
                            <p class="mt-1">{{ leave.rejection_reason }}</p>
                        </div>
                    </div>
                </Card>
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <Link :href="links.index" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</Link>
                <button v-if="leave.status === 'pending'" type="button" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700" @click="confirmOpen = true">
                    Batalkan Pengajuan
                </button>
            </div>
        </div>

        <ConfirmDialog
            :show="confirmOpen"
            title="Batalkan Pengajuan Cuti"
            message="Pengajuan yang dibatalkan akan dihapus dari daftar cuti Anda."
            confirm-text="Batalkan"
            @cancel="confirmOpen = false"
            @confirm="cancelLeave"
        />
    </AppShell>
</template>
