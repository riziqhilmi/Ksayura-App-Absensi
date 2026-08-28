<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Detail Pengajuan Cuti
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-emerald-500 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ substr($leave->employee->user->name ?? '', 0, 1) }}</span>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white">{{ $leave->employee->user->name ?? '-' }}</h2>
                                <p class="text-blue-50">{{ $leave->employee->position ?? 'Staff' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium
                            @if($leave->status == 'pending') bg-yellow-500/20 text-white
                            @elseif($leave->status == 'approved') bg-emerald-500/20 text-white
                            @else bg-red-500/20 text-white @endif">
                            @if($leave->status == 'pending')
                                <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>
                            @elseif($leave->status == 'approved')
                                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>
                            @endif
                            {{ ucfirst($leave->status) }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Informasi Cuti -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Detail Cuti
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Jenis Cuti</span>
                                    <span class="text-sm font-medium text-gray-800">{{ ucfirst($leave->leave_type) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Tanggal Mulai</span>
                                    <span class="text-sm font-medium text-gray-800">{{ date('d F Y', strtotime($leave->start_date)) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Tanggal Selesai</span>
                                    <span class="text-sm font-medium text-gray-800">{{ date('d F Y', strtotime($leave->end_date)) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Durasi</span>
                                    @php
                                        $start = \Carbon\Carbon::parse($leave->start_date);
                                        $end = \Carbon\Carbon::parse($leave->end_date);
                                        $days = $start->diffInDays($end) + 1;
                                    @endphp
                                    <span class="text-sm font-medium text-gray-800">{{ $days }} hari</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($leave->status == 'approved') bg-emerald-100 text-emerald-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan & Informasi -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Alasan & Informasi
                            </h3>
                            <div class="space-y-3">
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-500 mb-1">Alasan</p>
                                    <p class="text-sm text-gray-800">{{ $leave->reason ?? '-' }}</p>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Tanggal Pengajuan</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $leave->created_at->format('d F Y H:i') }}</span>
                                </div>
                                @if($leave->status != 'pending')
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-500">Diproses Oleh</span>
                                        <span class="text-sm font-medium text-gray-800">{{ $leave->approver->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-500">Tanggal Diproses</span>
                                        <span class="text-sm font-medium text-gray-800">{{ $leave->approved_at ? date('d F Y H:i', strtotime($leave->approved_at)) : '-' }}</span>
                                    </div>
                                    @if($leave->status == 'rejected' && $leave->rejection_reason)
                                        <div class="p-4 bg-red-50 rounded-xl border border-red-200">
                                            <p class="text-sm text-red-600 font-medium">Alasan Ditolak</p>
                                            <p class="text-sm text-red-700 mt-1">{{ $leave->rejection_reason }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex flex-wrap items-center justify-end space-x-3">
                        <a href="{{ route('owner.leaves.index') }}"
                            class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                            Kembali
                        </a>
                        @if($leave->status == 'pending')
                            <button onclick="approveLeave({{ $leave->id }})"
                                    class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-blue-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Setujui
                                </span>
                            </button>
                            <button onclick="rejectLeave({{ $leave->id }})"
                                    class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-500 text-white font-medium rounded-xl hover:from-red-700 hover:to-red-600 transition duration-200 shadow-md hover:shadow-lg">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reject (sama seperti di index) -->
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Tolak Pengajuan Cuti</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="rejectForm" class="space-y-4">
                @csrf
                <input type="hidden" id="rejectLeaveId" name="leave_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea id="rejectionReason" name="rejection_reason" rows="4" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeRejectModal()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-500 text-white rounded-xl hover:from-red-700 hover:to-red-600 transition shadow-md">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let rejectLeaveId = null;

        function approveLeave(id) {
            if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti ini?')) return;

            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            fetch(`/owner/leaves/${id}/approve`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
        }

        function rejectLeave(id) {
            rejectLeaveId = id;
            document.getElementById('rejectLeaveId').value = id;
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
            document.getElementById('rejectionReason').value = '';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
            rejectLeaveId = null;
        }

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const id = document.getElementById('rejectLeaveId').value;

            fetch(`/owner/leaves/${id}/reject`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rejection_reason: data.rejection_reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Tolak';
                closeRejectModal();
            });
        });

        // Close modal on click outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });
    </script>
    @endpush
</x-app-layout>