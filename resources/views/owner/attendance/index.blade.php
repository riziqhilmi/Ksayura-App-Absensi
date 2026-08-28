<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Monitoring Absensi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
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
                            <p class="text-sm font-medium text-gray-500">Hadir</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $stats['present'] }}</p>
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
                            <p class="text-sm font-medium text-gray-500">Terlambat</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['late'] }}</p>
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
                            <p class="text-sm font-medium text-gray-500">Tidak Hadir</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['absent'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Cuti</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $stats['leave'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
                <form method="GET" action="{{ route('owner.attendance.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md">
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
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($attendances as $attendance)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ date('d/m/Y', strtotime($attendance->date)) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-sm font-bold">
                                                {{ substr($attendance->employee->user->name ?? '', 0, 1) }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-800">{{ $attendance->employee->user->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $attendance->check_in_time ? date('H:i', strtotime($attendance->check_in_time)) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($attendance->latitude_in && $attendance->longitude_in)
                                            <button onclick="showLocation({{ $attendance->latitude_in }}, {{ $attendance->longitude_in }}, '{{ $attendance->employee->user->name }}')" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Lihat Lokasi
                                                </span>
                                            </button>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($attendance->status == 'present') bg-emerald-100 text-emerald-800
                                            @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                            @elseif($attendance->status == 'half_day') bg-blue-100 text-blue-800
                                            @elseif($attendance->status == 'leave') bg-purple-100 text-purple-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($attendance->status == 'present')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            @elseif($attendance->status == 'late')
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span>
                                            @elseif($attendance->status == 'half_day')
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                            @elseif($attendance->status == 'leave')
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1.5"></span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('owner.attendance.show', $attendance) }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition duration-150">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <p class="text-lg font-medium">Belum ada data absensi</p>
                                        <p class="text-sm">Data absensi akan muncul di sini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showLocation(lat, lng, name) {
            // Open Google Maps in new tab
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        }
    </script>
    @endpush
</x-app-layout>