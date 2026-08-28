<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Manajemen Shift
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Shift</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $shifts->count() }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Shift Aktif</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $activeShifts }}</p>
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
                            <p class="text-sm font-medium text-gray-500">Shift Nonaktif</p>
                            <p class="text-2xl font-bold text-red-600">{{ $inactiveShifts }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button Tambah Shift -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Cari shift..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <select class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <a href="{{ route('owner.shifts.create') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Shift
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Shift</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Mulai</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Selesai</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Istirahat</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toleransi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white font-bold">
                                                {{ substr($shift->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $shift->name }}</p>
                                                @if($shift->notes)
                                                    <p class="text-xs text-gray-500">{{ Str::limit($shift->notes, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->start_time)) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->end_time)) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($shift->break_start && $shift->break_end)
                                            <span class="text-sm text-gray-600">
                                                {{ date('H:i', strtotime($shift->break_start)) }} - {{ date('H:i', strtotime($shift->break_end)) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">{{ $shift->grace_period }} menit</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($shift->status == 'active') bg-emerald-100 text-emerald-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($shift->status == 'active')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            @endif
                                            {{ $shift->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('owner.shifts.show', $shift) }}" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition duration-150" 
                                               title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('owner.shifts.edit', $shift) }}" 
                                               class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-150" 
                                               title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button onclick="toggleStatus('{{ $shift->id }}', '{{ $shift->status }}')" 
                                                    class="p-2 {{ $shift->status == 'active' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-xl transition duration-150" 
                                                    title="{{ $shift->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                @if($shift->status == 'active')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                            <form id="delete-form-{{ $shift->id }}" action="{{ route('owner.shifts.destroy', $shift) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete('{{ $shift->id }}')" 
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition duration-150" 
                                                        title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-lg font-medium">Belum ada shift</p>
                                        <p class="text-sm">Tambahkan shift pertama Anda</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus shift ini?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }

        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const message = currentStatus === 'active' 
                ? 'Apakah Anda yakin ingin menonaktifkan shift ini?' 
                : 'Apakah Anda yakin ingin mengaktifkan shift ini?';
            
            if (confirm(message)) {
                fetch(`/owner/shifts/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal mengubah status shift');
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan');
                });
            }
        }
    </script>
    @endpush
</x-app-layout>