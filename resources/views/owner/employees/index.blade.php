<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                Manajemen Karyawan
            </h2>
            <a href="{{ route('owner.employees.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                </svg>
                Tambah Karyawan
            </a>
        </div>
    </x-slot>

    @php
        $activePercentage = $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100) : 0;
        $averageDailyRate = $employees->avg('daily_rate') ?? 0;

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

        $employeeStatusLabels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'resigned' => 'Resign',
        ];

        $employeeStatusColors = [
            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'resigned' => 'bg-red-50 text-red-700 ring-red-200',
        ];
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Karyawan</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalEmployees }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7m10 0h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Karyawan Aktif</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeEmployees }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Dari total karyawan</span>
                            <span class="font-bold text-emerald-600">{{ $activePercentage }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $activePercentage }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-500">Rata-rata Gaji Harian</p>
                            <p class="mt-2 truncate text-2xl font-bold text-slate-900">
                                Rp {{ number_format($averageDailyRate, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Daftar Karyawan</h3>
                        <p class="mt-1 text-sm text-slate-500">Cari, filter, dan kelola data karyawan.</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchEmployee" placeholder="Cari nama karyawan..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>

                        <select id="filterStatus" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="resigned">Resign</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Karyawan</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 lg:table-cell">Posisi</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Gaji</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Absen Hari Ini</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 xl:table-cell">Status</th>
                                <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="employeeTableBody">
                            @forelse($employees as $employee)
                                @php
                                    $todayAttendance = $employee->attendances->first();
                                    $attendanceStatus = $todayAttendance?->status ?? 'not_started';
                                    $attendanceClass = $attendanceColors[$attendanceStatus] ?? $attendanceColors['not_started'];
                                    $employeeStatusClass = $employeeStatusColors[$employee->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                                @endphp
                                <tr class="employee-row transition hover:bg-slate-50" data-name="{{ strtolower($employee->user->name) }}" data-status="{{ $employee->status }}">
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-emerald-500 text-sm font-bold text-white">
                                                {{ substr($employee->user->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900">{{ $employee->user->name }}</p>
                                                <p class="truncate text-xs text-slate-500">{{ $employee->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-600">{{ $employee->employee_code }}</span>
                                    </td>
                                    <td class="hidden px-5 py-4 lg:table-cell">
                                        <span class="text-sm text-slate-600">{{ $employee->position ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="whitespace-nowrap text-sm font-bold text-emerald-600">Rp {{ number_format($employee->daily_rate, 0, ',', '.') }}</p>
                                        <p class="text-xs text-slate-400">per hari</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $attendanceClass }}">
                                                {{ $attendanceLabels[$attendanceStatus] ?? ucfirst($attendanceStatus) }}
                                            </span>
                                            @if($todayAttendance?->check_in_time)
                                                <p class="text-xs text-slate-500">
                                                    {{ $todayAttendance->check_in_time->format('H:i') }}
                                                    @if($todayAttendance->check_out_time)
                                                        - {{ $todayAttendance->check_out_time->format('H:i') }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="hidden px-5 py-4 xl:table-cell">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $employeeStatusClass }}">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $employee->status == 'active' ? 'bg-emerald-500' : ($employee->status == 'inactive' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                            {{ $employeeStatusLabels[$employee->status] ?? ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('owner.employees.show', $employee) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('owner.employees.calendar', $employee) }}" class="rounded-xl p-2 text-violet-600 transition hover:bg-violet-50" title="Kalender">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('owner.employees.edit', $employee) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <button onclick="confirmDelete(this)" class="rounded-xl p-2 text-red-600 transition hover:bg-red-50" title="Hapus">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('owner.employees.destroy', $employee) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <svg class="mx-auto mb-4 h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7m10 0h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="text-lg font-bold text-slate-600">Belum ada karyawan</p>
                                        <p class="mt-1 text-sm text-slate-400">Tambahkan karyawan pertama untuk mulai mengelola data.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-4 md:hidden">
                @forelse($employees as $employee)
                    @php
                        $todayAttendance = $employee->attendances->first();
                        $attendanceStatus = $todayAttendance?->status ?? 'not_started';
                        $attendanceClass = $attendanceColors[$attendanceStatus] ?? $attendanceColors['not_started'];
                        $employeeStatusClass = $employeeStatusColors[$employee->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                    @endphp
                    <article class="employee-card rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md" data-name="{{ strtolower($employee->user->name) }}" data-status="{{ $employee->status }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-emerald-500 text-base font-bold text-white">
                                    {{ substr($employee->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $employee->user->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $employee->user->email }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $employeeStatusClass }}">
                                {{ $employeeStatusLabels[$employee->status] ?? ucfirst($employee->status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Kode</p>
                                <p class="mt-1 font-mono font-semibold text-slate-700">{{ $employee->employee_code }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Posisi</p>
                                <p class="mt-1 truncate font-semibold text-slate-700">{{ $employee->position ?? 'Staff' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Gaji Harian</p>
                                <p class="mt-1 font-bold text-emerald-600">Rp {{ number_format($employee->daily_rate, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Absen Hari Ini</p>
                                <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $attendanceClass }}">
                                    {{ $attendanceLabels[$attendanceStatus] ?? ucfirst($attendanceStatus) }}
                                </span>
                                @if($todayAttendance?->check_in_time)
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $todayAttendance->check_in_time->format('H:i') }}
                                        @if($todayAttendance->check_out_time)
                                            - {{ $todayAttendance->check_out_time->format('H:i') }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-1 border-t border-slate-100 pt-3">
                            <a href="{{ route('owner.employees.show', $employee) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('owner.employees.calendar', $employee) }}" class="rounded-xl p-2 text-violet-600 transition hover:bg-violet-50" title="Kalender">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </a>
                            <a href="{{ route('owner.employees.edit', $employee) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <button onclick="confirmDelete(this)" class="rounded-xl p-2 text-red-600 transition hover:bg-red-50" title="Hapus">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            <form action="{{ route('owner.employees.destroy', $employee) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <svg class="mx-auto mb-4 h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7m10 0h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-lg font-bold text-slate-600">Belum ada karyawan</p>
                        <p class="mt-1 text-sm text-slate-400">Tambahkan karyawan pertama untuk mulai mengelola data.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(button) {
                if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
                    button.parentElement.querySelector('form').submit();
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchEmployee');
                const filterStatus = document.getElementById('filterStatus');

                function filterEmployees() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const statusFilter = filterStatus.value;
                    const employees = document.querySelectorAll('.employee-row, .employee-card');

                    employees.forEach((employee) => {
                        const name = employee.dataset.name || '';
                        const status = employee.dataset.status || '';
                        const matchSearch = name.includes(searchTerm);
                        const matchStatus = statusFilter === '' || status === statusFilter;

                        employee.style.display = matchSearch && matchStatus ? '' : 'none';
                    });
                }

                searchInput.addEventListener('input', filterEmployees);
                filterStatus.addEventListener('change', filterEmployees);
            });
        </script>
    @endpush
</x-app-layout>
