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
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm">Detail Pengajuan Cuti</p>
                            <h2 class="text-2xl font-bold text-white">{{ ucfirst($leave->leave_type) }}</h2>
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
                            </div>
                        </div>

                        <!-- Status & Informasi -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Status & Informasi
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($leave->status == 'approved') bg-emerald-100 text-emerald-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
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
                                        <div class="p-4 bg-red-50 rounded-xl border border-red-200 mt-4">
                                            <p class="text-sm text-red-600 font-medium">Alasan Ditolak</p>
                                            <p class="text-sm text-red-700 mt-1">{{ $leave->rejection_reason }}</p>
                                        </div>
                                    @endif
                                    @if($leave->status == 'approved')
                                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 mt-4">
                                            <p class="text-sm text-emerald-600 font-medium">✅ Cuti Disetujui</p>
                                            <p class="text-sm text-emerald-700 mt-1">Selamat! Pengajuan cuti Anda telah disetujui.</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Alasan -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Alasan Cuti
                        </h3>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-800">{{ $leave->reason ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex flex-wrap items-center justify-end space-x-3">
                        <a href="{{ route('employee.leaves.my') }}"
                            class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                            Kembali
                        </a>
                        @if($leave->status == 'pending')
                            <button onclick="cancelLeave({{ $leave->id }})"
                                    class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-500 text-white font-medium rounded-xl hover:from-red-700 hover:to-red-600 transition duration-200 shadow-md hover:shadow-lg">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Batalkan Pengajuan
                                </span>
                            </button>
                            <form id="cancel-form-{{ $leave->id }}" 
                                  action="{{ route('employee.leaves.destroy', $leave) }}" 
                                  method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function cancelLeave(id) {
            if (confirm('Apakah Anda yakin ingin membatalkan pengajuan cuti ini?')) {
                document.getElementById('cancel-form-' + id).submit();
            }
        }
    </script>
    @endpush
</x-app-layout>