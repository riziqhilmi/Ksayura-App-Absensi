<x-layouts.owner>
    <x-slot name="header">
        Manajemen Karyawan
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Karyawan</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalEmployees }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Karyawan Aktif</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $activeEmployees }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Rata-rata Gaji</p>
                        <p class="text-2xl font-bold text-gray-800">
                            Rp {{ number_format($employees->avg('base_salary') ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Tambah Karyawan -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" placeholder="Cari karyawan..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                    <option value="resigned">Resign</option>
                </select>
            </div>
            <a href="{{ route('owner.employees.create') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Karyawan
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absen Hari Ini</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white font-bold">
                                            {{ substr($employee->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $employee->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $employee->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-mono text-gray-600">{{ $employee->employee_code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ $employee->position ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst($employee->salary_type) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $todayAttendance = $employee->attendances->first();
                                        $attendanceStatus = $todayAttendance?->status ?? 'not_started';
                                        $attendanceLabels = [
                                            'present' => 'Hadir',
                                            'late' => 'Terlambat',
                                            'absent' => 'Tidak Hadir',
                                            'half_day' => 'Setengah Hari',
                                            'leave' => 'Cuti',
                                            'not_started' => 'Belum Absen',
                                        ];
                                    @endphp

                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($attendanceStatus === 'present') bg-emerald-100 text-emerald-800
                                            @elseif($attendanceStatus === 'late') bg-yellow-100 text-yellow-800
                                            @elseif($attendanceStatus === 'half_day') bg-blue-100 text-blue-800
                                            @elseif($attendanceStatus === 'leave') bg-purple-100 text-purple-800
                                            @elseif($attendanceStatus === 'absent') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ $attendanceLabels[$attendanceStatus] ?? ucfirst($attendanceStatus) }}
                                        </span>

                                        @if($todayAttendance?->check_in_time)
                                            <p class="text-xs text-gray-500">
                                                In {{ $todayAttendance->check_in_time->format('H:i') }}
                                                @if($todayAttendance->check_out_time)
                                                    - Out {{ $todayAttendance->check_out_time->format('H:i') }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($employee->status == 'active') bg-emerald-100 text-emerald-800
                                        @elseif($employee->status == 'inactive') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        @if($employee->status == 'active')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        @elseif($employee->status == 'inactive')
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                        @endif
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('owner.employees.show', $employee) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition duration-150" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('owner.employees.edit', $employee) }}" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-150" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button onclick="confirmDelete('{{ $employee->id }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition duration-150" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                        <form id="delete-form-{{ $employee->id }}" action="{{ route('owner.employees.destroy', $employee) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium">Belum ada karyawan</p>
                                    <p class="text-sm">Tambahkan karyawan pertama Anda sekarang</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
    @endpush
</x-layouts.owner>
