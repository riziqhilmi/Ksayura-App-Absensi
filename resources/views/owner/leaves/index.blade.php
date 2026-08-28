<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Pengajuan Cuti Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Pengajuan</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Menunggu</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Disetujui</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ditolak</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
                <form method="GET" action="{{ route('owner.leaves.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                        <select name="employee" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Cuti</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengajuan</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($leaveRequests as $leave)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-sm font-bold">
                                                {{ substr($leave->employee->user->name ?? '', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $leave->employee->user->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $leave->employee->employee_code ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-800">{{ ucfirst($leave->leave_type) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">
                                            <div>{{ date('d/m/Y', strtotime($leave->start_date)) }}</div>
                                            <div class="text-xs text-gray-400">s/d</div>
                                            <div>{{ date('d/m/Y', strtotime($leave->end_date)) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $start = \Carbon\Carbon::parse($leave->start_date);
                                            $end = \Carbon\Carbon::parse($leave->end_date);
                                            $days = $start->diffInDays($end) + 1;
                                        @endphp
                                        <span class="text-sm font-medium text-gray-800">{{ $days }} hari</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($leave->status == 'approved') bg-emerald-100 text-emerald-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($leave->status == 'pending')
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span>
                                            @elseif($leave->status == 'approved')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            @endif
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $leave->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('owner.leaves.show', $leave) }}" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition duration-150" 
                                               title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            @if($leave->status == 'pending')
                                                <button onclick="approveLeave({{ $leave->id }})" 
                                                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-150" 
                                                        title="Setujui">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </button>
                                                <button onclick="rejectLeave({{ $leave->id }})" 
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition duration-150" 
                                                        title="Tolak">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-lg font-medium">Belum ada pengajuan cuti</p>
                                        <p class="text-sm">Pengajuan cuti dari karyawan akan muncul di sini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $leaveRequests->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
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