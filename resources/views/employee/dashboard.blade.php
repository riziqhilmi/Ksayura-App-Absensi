<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </span>
                Dashboard Karyawan
            </h2>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
    </x-slot>

    @php
        $attendanceLabels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'half_day' => 'Setengah Hari',
            'leave' => 'Cuti',
            'auto_checkout' => 'Auto Checkout',
            'not_started' => 'Belum Absen',
        ];

        $attendanceColors = [
            'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'late' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'half_day' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'leave' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'auto_checkout' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'absent' => 'bg-red-50 text-red-700 ring-red-200',
            'not_started' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-emerald-500 p-6 shadow-lg">
                <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-white/5"></div>
                
                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-2 border-white/30 bg-white/20 text-2xl font-bold text-white backdrop-blur">
                            {{ Auth::user()->name[0] }}
                        </div>
                        <div>
                            <p class="text-sm text-blue-100">Selamat datang kembali</p>
                            <h3 class="text-xl font-bold text-white sm:text-2xl">{{ Auth::user()->name }}</h3>
                            <p class="mt-0.5 text-sm text-blue-100">{{ $employee->position ?? 'Staff' }} · {{ $employee->employee_code }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-left sm:mt-0 sm:text-right">
                        <p class="text-sm text-blue-100">Status Absensi Hari Ini</p>
                        @if($todayAttendance)
                            @php
                                $status = $todayAttendance->check_in_time && !$todayAttendance->check_out_time ? 'checked_in' : 
                                          ($todayAttendance->check_out_time ? 'complete' : $todayAttendance->status);
                            @endphp
                            <span class="mt-1 inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                                @if($status == 'checked_in')
                                    <span class="mr-1.5 h-1.5 w-1.5 animate-pulse rounded-full bg-green-400"></span>
                                    Checked In
                                @elseif($status == 'complete')
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                                    Complete
                                @else
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-yellow-400"></span>
                                    {{ ucfirst($todayAttendance->status) }}
                                @endif
                            </span>
                        @else
                            <span class="mt-1 inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                Belum Absen
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Kehadiran</p>
                            <p class="mt-1 text-xl font-bold text-gray-800">{{ $stats['attendance_percentage'] }}%</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Hadir</p>
                            <p class="mt-1 text-xl font-bold text-emerald-600">{{ $stats['present'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Pending Cuti</p>
                            <p class="mt-1 text-xl font-bold text-amber-600">{{ $pendingLeaves }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Gaji Terakhir</p>
                            <p class="mt-1 text-sm font-bold text-blue-600 truncate">
                                Rp {{ number_format($latestSalary->total_salary ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <a href="{{ route('employee.attendance.my') }}" class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition group-hover:bg-blue-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Absensi</p>
                            <p class="text-sm text-gray-500">Lihat riwayat absensi</p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <a href="{{ route('employee.leaves.create') }}" class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 transition group-hover:bg-amber-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Ajukan Cuti</p>
                            <p class="text-sm text-gray-500">Buat pengajuan cuti baru</p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <a href="{{ route('employee.calendar.index') }}" class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 transition group-hover:bg-purple-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Kalender Kerja</p>
                            <p class="text-sm text-gray-500">Lihat jadwal shift & libur</p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Recent Activities -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="flex items-center text-base font-semibold text-gray-800">
                        <svg class="mr-2 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aktivitas Terbaru
                    </h3>
                    <span class="text-xs text-gray-400">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start gap-3 px-6 py-3.5 transition hover:bg-gray-50">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full 
                                {{ $activity['type'] == 'attendance' ? 
                                    ($activity['color'] == 'green' ? 'bg-emerald-100' : 
                                     ($activity['color'] == 'yellow' ? 'bg-amber-100' : 
                                     ($activity['color'] == 'red' ? 'bg-red-100' : 'bg-blue-100'))) : 
                                    ($activity['color'] == 'yellow' ? 'bg-amber-100' : 
                                     ($activity['color'] == 'green' ? 'bg-emerald-100' : 'bg-red-100')) }}">
                                <svg class="h-4 w-4 
                                    {{ $activity['type'] == 'attendance' ? 
                                        ($activity['color'] == 'green' ? 'text-emerald-600' : 
                                         ($activity['color'] == 'yellow' ? 'text-amber-600' : 
                                         ($activity['color'] == 'red' ? 'text-red-600' : 'text-blue-600'))) : 
                                        ($activity['color'] == 'yellow' ? 'text-amber-600' : 
                                         ($activity['color'] == 'green' ? 'text-emerald-600' : 'text-red-600')) }}" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $activity['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                            </div>
                            <div class="shrink-0">
                                <span class="text-xs text-gray-400">{{ $activity['time'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="font-medium text-gray-500">Belum ada aktivitas</p>
                            <p class="mt-1 text-sm text-gray-400">Mulai absensi atau ajukan cuti</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>