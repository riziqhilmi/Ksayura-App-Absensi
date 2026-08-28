<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Dashboard Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-blue-500 to-emerald-500 rounded-2xl shadow-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Selamat datang kembali</p>
                        <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="text-blue-100 mt-1">{{ $employee->position ?? 'Staff' }} | {{ $employee->employee_code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-100">Hari ini</p>
                        <p class="text-lg font-semibold">{{ now()->format('d F Y') }}</p>
                        @if($todayAttendance)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white mt-1">
                                @if($todayAttendance->check_in_time && !$todayAttendance->check_out_time)
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5 animate-pulse"></span>
                                    Checked In
                                @elseif($todayAttendance->check_out_time)
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5"></span>
                                    Complete
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5"></span>
                                    {{ ucfirst($todayAttendance->status) }}
                                @endif
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white mt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                Belum Absen
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Kehadiran Bulan Ini</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['attendance_percentage'] }}%</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Hadir</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $stats['present'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Cuti Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $pendingLeaves }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Gaji Terakhir</p>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp {{ number_format($latestSalary->total_salary ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a href="{{ route('employee.attendance.my') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 group-hover:bg-blue-200 transition duration-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Absensi</p>
                            <p class="text-sm text-gray-500">Lihat riwayat absensi</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <a href="{{ route('employee.leaves.create') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 group-hover:bg-yellow-200 transition duration-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Ajukan Cuti</p>
                            <p class="text-sm text-gray-500">Buat pengajuan cuti baru</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <a href="{{ route('employee.salaries.my') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 group-hover:bg-purple-200 transition duration-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Gaji</p>
                            <p class="text-sm text-gray-500">Lihat riwayat gaji</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recentActivities as $activity)
                        <div class="px-6 py-4 hover:bg-gray-50 transition duration-150">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($activity['type'] == 'attendance')
                                        <div class="w-10 h-10 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($activity['icon'] == 'check-circle')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @elseif($activity['icon'] == 'clock')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @elseif($activity['icon'] == 'x-circle')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                @endif
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ $activity['title'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="text-xs text-gray-400">{{ $activity['time'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-lg font-medium">Belum ada aktivitas</p>
                            <p class="text-sm">Mulai absensi atau ajukan cuti</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>