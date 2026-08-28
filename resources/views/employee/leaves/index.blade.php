<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Riwayat Pengajuan Cuti
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

            <!-- Actions -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <select id="status-filter" onchange="window.location.href='?status='+this.value"
                            class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <a href="{{ route('employee.leaves.create') }}" 
                   class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-medium rounded-xl hover:from-yellow-600 hover:to-orange-600 transition duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajukan Cuti
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Cuti</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($leaves as $leave)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $leave->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-800">{{ ucfirst($leave->leave_type) }}</span>
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
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('employee.leaves.show', $leave) }}" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition duration-150" 
                                               title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            @if($leave->status == 'pending')
                                                <button onclick="cancelLeave({{ $leave->id }})" 
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition duration-150" 
                                                        title="Batalkan">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                                <form id="cancel-form-{{ $leave->id }}" 
                                                      action="{{ route('employee.leaves.destroy', $leave) }}" 
                                                      method="POST" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-lg font-medium">Belum ada pengajuan cuti</p>
                                        <p class="text-sm">Ajukan cuti pertama Anda</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $leaves->links() }}
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