<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </span>
                Dashboard Owner
            </h2>
        </div>
    </x-slot>

    @php
        $totalEmployees = max((int) ($stats['total_employees'] ?? 0), 0);
        $activeEmployees = max((int) ($stats['active_employees'] ?? 0), 0);
        $activePercentage = $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100) : 0;
        $monthlyTotal = max((int) ($stats['monthly_attendance']['total'] ?? 0), 0);

        $monthlyRows = [
            ['label' => 'Hadir', 'value' => $stats['monthly_attendance']['present'] ?? 0, 'bar' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
            ['label' => 'Terlambat', 'value' => $stats['monthly_attendance']['late'] ?? 0, 'bar' => 'bg-amber-500', 'text' => 'text-amber-600'],
            ['label' => 'Tidak Hadir', 'value' => $stats['monthly_attendance']['absent'] ?? 0, 'bar' => 'bg-red-500', 'text' => 'text-red-600'],
            ['label' => 'Setengah Hari / Auto CO', 'value' => ($stats['monthly_attendance']['half_day'] ?? 0) + ($stats['monthly_attendance']['auto_checkout'] ?? 0), 'bar' => 'bg-indigo-500', 'text' => 'text-indigo-600'],
        ];
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-700 via-teal-600 to-blue-700 shadow-lg">
                <div class="flex flex-col gap-6 px-6 py-7 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-100">Selamat datang kembali</p>
                        <h3 class="mt-1 text-2xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="mt-2 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/20">
                            Owner Kantor Sayur
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/15 px-5 py-4 text-left ring-1 ring-white/20 sm:text-right">
                        <p class="text-sm font-semibold">{{ now()->format('l, d F Y') }}</p>
                        <p class="mt-1 text-2xl font-bold">{{ now()->format('H:i') }} WIB</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Karyawan</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_employees'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-xl bg-emerald-50 p-2">
                            <p class="font-bold text-emerald-700">{{ $stats['active_employees'] }}</p>
                            <p class="text-slate-500">Aktif</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-2">
                            <p class="font-bold text-amber-700">{{ $stats['inactive_employees'] }}</p>
                            <p class="text-slate-500">Nonaktif</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-2">
                            <p class="font-bold text-blue-700">+{{ $stats['new_employees'] }}</p>
                            <p class="text-slate-500">Baru</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Kehadiran Hari Ini</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['today_attendance'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">
                        <span class="font-bold text-emerald-600">{{ $stats['attendance_percentage'] }}%</span>
                        dari {{ $stats['active_employees'] }} karyawan aktif
                    </p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-xl bg-emerald-50 p-2">
                            <p class="font-bold text-emerald-700">{{ $stats['today_present'] }}</p>
                            <p class="text-slate-500">Hadir</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-2">
                            <p class="font-bold text-amber-700">{{ $stats['today_late'] }}</p>
                            <p class="text-slate-500">Telat</p>
                        </div>
                        <div class="rounded-xl bg-red-50 p-2">
                            <p class="font-bold text-red-700">{{ $stats['today_absent'] }}</p>
                            <p class="text-slate-500">Absen</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Estimasi Gaji Bulanan</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($stats['total_salary'] ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 rounded-xl bg-slate-50 p-3 text-sm">
                        <p class="text-slate-500">Rata-rata gaji harian</p>
                        <p class="mt-1 font-bold text-slate-900">Rp {{ number_format($stats['average_salary'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Estimasi 30 hari kerja</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Pengajuan Cuti</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_leaves'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Menunggu</span>
                            <span class="font-bold {{ $stats['pending_leaves'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $stats['pending_leaves'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Disetujui</span>
                            <span class="font-bold text-emerald-600">{{ $stats['approved_leaves'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Ditolak</span>
                            <span class="font-bold text-red-600">{{ $stats['rejected_leaves'] }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Status Karyawan</h3>
                            <p class="text-sm text-slate-500">Ringkasan komposisi karyawan saat ini</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                        <div class="relative h-32 w-32 shrink-0">
                            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 128 128">
                                <circle cx="64" cy="64" r="52" stroke="#e2e8f0" stroke-width="14" fill="none" />
                                <circle cx="64" cy="64" r="52" stroke="#10b981" stroke-width="14" fill="none" stroke-linecap="round" stroke-dasharray="{{ ($activePercentage / 100) * 326.73 }} 326.73" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-slate-900">{{ $activePercentage }}%</span>
                                <span class="text-xs font-medium text-slate-500">Aktif</span>
                            </div>
                        </div>

                        <div class="w-full space-y-3">
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <span class="flex items-center text-slate-600"><span class="mr-3 h-3 w-3 rounded-full bg-emerald-500"></span>Aktif</span>
                                <span class="font-bold text-slate-900">{{ $stats['active_employees'] }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <span class="flex items-center text-slate-600"><span class="mr-3 h-3 w-3 rounded-full bg-amber-500"></span>Tidak Aktif</span>
                                <span class="font-bold text-slate-900">{{ $stats['inactive_employees'] }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <span class="flex items-center text-slate-600"><span class="mr-3 h-3 w-3 rounded-full bg-red-500"></span>Resign</span>
                                <span class="font-bold text-slate-900">{{ $stats['resigned_employees'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Kehadiran Bulan Ini</h3>
                            <p class="text-sm text-slate-500">Total catatan: {{ $stats['monthly_attendance']['total'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($monthlyRows as $row)
                            @php
                                $width = $monthlyTotal > 0 ? min(100, round(($row['value'] / $monthlyTotal) * 100, 2)) : 0;
                            @endphp
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-600">{{ $row['label'] }}</span>
                                    <span class="font-bold {{ $row['text'] }}">{{ $row['value'] }}</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $row['bar'] }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="flex items-center text-lg font-bold text-slate-900">
                            <svg class="mr-2 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aktivitas Terbaru
                        </h3>
                        <p class="text-sm text-slate-500">Update terakhir {{ now()->format('d F Y H:i') }}</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-100">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                            @php
                                $tone = match (true) {
                                    $activity['type'] == 'attendance' && $activity['status'] == 'present' => 'bg-emerald-50 text-emerald-700',
                                    $activity['type'] == 'attendance' && $activity['status'] == 'late' => 'bg-amber-50 text-amber-700',
                                    $activity['type'] == 'attendance' && $activity['status'] == 'absent' => 'bg-red-50 text-red-700',
                                    $activity['type'] == 'leave' && $activity['status'] == 'pending' => 'bg-amber-50 text-amber-700',
                                    $activity['type'] == 'leave' && $activity['status'] == 'approved' => 'bg-emerald-50 text-emerald-700',
                                    $activity['type'] == 'leave' && $activity['status'] == 'rejected' => 'bg-red-50 text-red-700',
                                    default => 'bg-blue-50 text-blue-700',
                                };
                            @endphp
                            <div class="flex items-start gap-4 bg-white p-4 transition hover:bg-slate-50">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $tone }}">
                                    <span class="text-sm font-bold">{{ substr($activity['user'], 0, 1) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $activity['user'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $activity['action'] }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-slate-400">{{ $activity['time'] }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="px-4 py-10 text-center text-slate-400">
                            <svg class="mx-auto mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-sm font-medium">Belum ada aktivitas</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
