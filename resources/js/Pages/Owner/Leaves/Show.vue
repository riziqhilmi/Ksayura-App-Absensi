<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    leave: { type: Object, required: true },
    links: { type: Object, required: true },
});

const confirmState = ref({ show: false, title: '', message: '', action: null, confirmText: 'Lanjutkan' });
const rejectModal = ref(false);
const rejectReason = ref('');
const processing = ref(false);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const statusLabels = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' };
const statusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    rejected: 'bg-red-50 text-red-700 ring-red-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');

const askApprove = () => {
    confirmState.value = {
        show: true,
        title: 'Setujui Cuti',
        message: `Setujui pengajuan cuti ${props.leave.employee?.name || '-'}?`,
        confirmText: 'Setujui',
        action: approve,
    };
};
const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
const approve = async () => {
    processing.value = true;
    const response = await fetch(props.leave.urls.approve, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
    });
    processing.value = false;
    if (response.ok) router.reload({ preserveScroll: true });
};
const submitReject = async () => {
    processing.value = true;
    const response = await fetch(props.leave.urls.reject, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: JSON.stringify({ rejection_reason: rejectReason.value }),
    });
    processing.value = false;
    if (response.ok) {
        rejectModal.value = false;
        router.reload({ preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Detail Pengajuan Cuti" />

    <AppShell>
        <div class="mx-auto max-w-4xl space-y-6">
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="bg-gradient-to-r from-blue-600 to-emerald-500 px-6 py-6 text-white">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl font-bold">
                                {{ leave.employee?.name?.charAt(0) || 'K' }}
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold">{{ leave.employee?.name || '-' }}</h1>
                                <p class="text-sm text-blue-50">{{ leave.employee?.position || 'Staff' }}</p>
                            </div>
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-bold ring-1" :class="statusClass(leave.status)">
                            {{ statusLabels[leave.status] || leave.status }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <h2 class="font-bold">Detail Cuti</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex justify-between py-3"><dt class="text-slate-500">Jenis Cuti</dt><dd class="font-semibold">{{ leave.leave_type_label }}</dd></div>
                        <div class="flex justify-between py-3"><dt class="text-slate-500">Tanggal Mulai</dt><dd class="font-semibold">{{ leave.start_date_long }}</dd></div>
                        <div class="flex justify-between py-3"><dt class="text-slate-500">Tanggal Selesai</dt><dd class="font-semibold">{{ leave.end_date_long }}</dd></div>
                        <div class="flex justify-between py-3"><dt class="text-slate-500">Durasi</dt><dd class="font-semibold">{{ leave.duration_days }} hari</dd></div>
                        <div class="flex justify-between py-3"><dt class="text-slate-500">Status</dt><dd><span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="statusClass(leave.status)">{{ statusLabels[leave.status] || leave.status }}</span></dd></div>
                    </dl>
                </Card>

                <Card>
                    <h2 class="font-bold">Alasan & Informasi</h2>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="font-semibold text-slate-500">Alasan</p>
                            <p class="mt-1 text-slate-700">{{ leave.reason || '-' }}</p>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Tanggal Pengajuan</span>
                            <span class="font-semibold">{{ leave.created_at_long }}</span>
                        </div>
                        <div v-if="leave.status !== 'pending'" class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Diproses Oleh</span>
                            <span class="font-semibold">{{ leave.approved_by || '-' }}</span>
                        </div>
                        <div v-if="leave.status !== 'pending'" class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Tanggal Diproses</span>
                            <span class="font-semibold">{{ leave.approved_at || '-' }}</span>
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
                <button v-if="leave.status === 'pending'" type="button" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" :disabled="processing" @click="askApprove">Setujui</button>
                <button v-if="leave.status === 'pending'" type="button" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60" :disabled="processing" @click="rejectModal = true">Tolak</button>
            </div>
        </div>

        <ConfirmDialog
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            :confirm-text="confirmState.confirmText"
            @cancel="confirmState.show = false"
            @confirm="runConfirmed"
        />

        <Modal :show="rejectModal" title="Tolak Pengajuan Cuti" @close="rejectModal = false">
            <form class="space-y-4" @submit.prevent="submitReject">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Alasan Penolakan</label>
                    <textarea v-model="rejectReason" required rows="4" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-100" />
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700" @click="rejectModal = false">Batal</button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60" :disabled="processing">
                        {{ processing ? 'Memproses...' : 'Tolak' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AppShell>
</template>
