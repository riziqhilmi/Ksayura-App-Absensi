<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import Modal from '../../../Components/Modal.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    leaveRequests: { type: Object, required: true },
    employees: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, required: true },
    links: { type: Object, required: true },
});

const filters = reactive({
    status: props.filters.status || '',
    employee: props.filters.employee || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});
const confirmState = ref({ show: false, title: '', message: '', action: null, confirmText: 'Lanjutkan' });
const rejectModal = ref(false);
const rejectReason = ref('');
const selectedLeave = ref(null);
const processing = ref(false);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const statusLabels = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' };
const statusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    rejected: 'bg-red-50 text-red-700 ring-red-200',
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200');
const statCards = [
    ['Total Pengajuan', 'total', 'text-slate-900'],
    ['Menunggu', 'pending', 'text-amber-600'],
    ['Disetujui', 'approved', 'text-emerald-600'],
    ['Ditolak', 'rejected', 'text-red-600'],
];

const applyFilter = () => router.get(props.links.index, filters, { preserveState: true, replace: true });
const askApprove = (leave) => {
    confirmState.value = {
        show: true,
        title: 'Setujui Cuti',
        message: `Setujui pengajuan cuti ${leave.employee?.name || '-'}?`,
        confirmText: 'Setujui',
        action: () => approve(leave),
    };
};
const openReject = (leave) => {
    selectedLeave.value = leave;
    rejectReason.value = '';
    rejectModal.value = true;
};
const runConfirmed = () => {
    const action = confirmState.value.action;
    confirmState.value.show = false;
    if (action) action();
};
const approve = async (leave) => {
    processing.value = true;
    const response = await fetch(leave.urls.approve, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
    });
    processing.value = false;
    if (response.ok) router.reload({ preserveScroll: true });
};
const submitReject = async () => {
    if (!selectedLeave.value) return;
    processing.value = true;
    const response = await fetch(selectedLeave.value.urls.reject, {
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
    <Head title="Pengajuan Cuti" />

    <AppShell>
        <div class="mx-auto max-w-7xl space-y-6">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Owner</p>
                <h1 class="text-2xl font-bold text-slate-950">Pengajuan Cuti Karyawan</h1>
                <p class="mt-1 text-sm text-slate-500">Tinjau, setujui, atau tolak pengajuan cuti karyawan.</p>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="item in statCards" :key="item[1]">
                    <p class="text-sm text-slate-500">{{ item[0] }}</p>
                    <p class="mt-2 text-3xl font-bold" :class="item[2]">{{ stats[item[1]] || 0 }}</p>
                </Card>
            </section>

            <Card>
                <form class="grid grid-cols-1 gap-3 md:grid-cols-5" @submit.prevent="applyFilter">
                    <select v-model="filters.status" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <select v-model="filters.employee" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                        <option value="">Semua Karyawan</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                    </select>
                    <input v-model="filters.start_date" type="date" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                    <input v-model="filters.end_date" type="date" class="rounded-lg border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-100">
                    <button class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                </form>
            </Card>

            <Card>
                <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="leave in leaveRequests.data" :key="leave.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold">{{ leave.employee?.name || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ leave.leave_type_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ leave.start_date_label }} - {{ leave.end_date_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ leave.duration_days }} hari</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusClass(leave.status)">{{ statusLabels[leave.status] || leave.status }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <Link :href="leave.urls.show" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Detail</Link>
                                        <button v-if="leave.status === 'pending'" type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="askApprove(leave)">Setujui</button>
                                        <button v-if="leave.status === 'pending'" type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="openReject(leave)">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="leaveRequests.data.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada pengajuan cuti</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <article v-for="leave in leaveRequests.data" :key="leave.id" class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ leave.employee?.name || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ leave.leave_type_label }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusClass(leave.status)">{{ statusLabels[leave.status] || leave.status }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <p><span class="block text-xs text-slate-400">Periode</span><b>{{ leave.start_date_label }} - {{ leave.end_date_label }}</b></p>
                            <p><span class="block text-xs text-slate-400">Durasi</span><b>{{ leave.duration_days }} hari</b></p>
                        </div>
                        <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-3">
                            <Link :href="leave.urls.show" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Detail</Link>
                            <button v-if="leave.status === 'pending'" type="button" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700" @click="askApprove(leave)">Setujui</button>
                            <button v-if="leave.status === 'pending'" type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" @click="openReject(leave)">Tolak</button>
                        </div>
                    </article>
                </div>

                <div class="mt-5">
                    <Pagination :links="leaveRequests.links" />
                </div>
            </Card>
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
                    <textarea v-model="rejectReason" required rows="4" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-100" placeholder="Masukkan alasan penolakan..." />
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
