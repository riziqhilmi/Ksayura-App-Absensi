<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                Kalender Karyawan
            </h2>
            <a href="{{ route('owner.employees.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 transition hover:text-blue-800">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $workingPercent = ($stats['total_days'] ?? 0) > 0 ? (($stats['working_days'] ?? 0) / $stats['total_days']) * 100 : 0;
        $holidayPercent = ($stats['total_days'] ?? 0) > 0 ? (($stats['holidays'] ?? 0) / $stats['total_days']) * 100 : 0;
    @endphp

    <div class="bg-gray-50 py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-col gap-5 bg-gradient-to-r from-blue-600 via-cyan-600 to-emerald-500 p-5 text-white sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/20 text-2xl font-bold text-white">
                            {{ substr($employee->user->name ?? '', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white/80">Kalender {{ $monthName }}</p>
                            <h3 class="truncate text-2xl font-bold">{{ $employee->user->name ?? '-' }}</h3>
                            <p class="mt-1 truncate text-sm text-white/80">{{ $employee->employee_code ?? '-' }} | {{ $employee->position ?? 'Staff' }}</p>
                            <p class="mt-1 truncate text-sm text-white/80">{{ $employee->user->email ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <a href="{{ route('owner.employees.calendar', ['employee' => $employee->id, 'month' => $month - 1, 'year' => $year]) }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25"
                           title="Bulan sebelumnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ route('owner.employees.calendar', ['employee' => $employee->id, 'month' => now()->month, 'year' => now()->year]) }}"
                           class="inline-flex h-10 items-center justify-center rounded-xl bg-white px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                            Hari Ini
                        </a>
                        <a href="{{ route('owner.employees.calendar', ['employee' => $employee->id, 'month' => $month + 1, 'year' => $year]) }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25"
                           title="Bulan berikutnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <span class="inline-flex h-10 items-center rounded-xl bg-white/15 px-3 text-xs font-medium text-white">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase text-gray-400">Total Hari</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $stats['total_days'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase text-gray-400">Hari Kerja</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['working_days'] ?? 0 }}</p>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100">
                        <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $workingPercent }}%"></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase text-gray-400">Libur Owner/Cuti</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['holidays'] ?? 0 }}</p>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100">
                        <div class="h-1.5 rounded-full bg-amber-500" style="width: {{ $holidayPercent }}%"></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase text-gray-400">Akhir Pekan</p>
                    <p class="mt-1 text-2xl font-bold text-gray-500">{{ $stats['weekends'] ?? 0 }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                <span class="mr-1 text-sm font-semibold text-gray-700">Legenda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Shift Kerja
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Libur Owner/Cuti
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600">
                    <span class="h-2.5 w-2.5 rounded-full border border-gray-300 bg-gray-200"></span> Akhir Pekan
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Hari Ini
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">
                    <span class="h-2.5 w-2.5 rounded-full border border-red-300 bg-red-100"></span> Kosong
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="grid grid-cols-7 border-b bg-gray-50">
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Min</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Sen</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Sel</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Rab</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Kam</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Jum</div>
                    <div class="py-2.5 text-center text-[10px] font-semibold uppercase text-gray-500 sm:py-3 sm:text-xs">Sab</div>
                </div>

                <div class="grid grid-cols-7 auto-rows-fr">
                    @php
                        $firstDayOffset = $firstDayOfMonth;
                        $totalCells = $daysInMonth + $firstDayOffset;
                        $rows = ceil($totalCells / 7);
                    @endphp

                    @for($row = 0; $row < $rows; $row++)
                        @for($col = 0; $col < 7; $col++)
                            @php
                                $cellDay = $row * 7 + $col - $firstDayOffset + 1;
                                $isValidDay = $cellDay >= 1 && $cellDay <= $daysInMonth;
                                $dateObj = $isValidDay ? Carbon\Carbon::createFromDate($year, $month, $cellDay) : null;
                                $dateStr = $dateObj ? $dateObj->format('Y-m-d') : null;
                                $dayData = $dateStr ? ($calendarData[$dateStr] ?? null) : null;
                                $isToday = $dateStr === $today;
                                $isWeekend = $dateObj && $dateObj->isWeekend();
                            @endphp

                            <div class="min-h-[82px] border-r border-b p-1.5 transition hover:bg-gray-50 sm:min-h-[118px] sm:p-2.5
                                {{ $isToday ? 'bg-blue-50 ring-2 ring-blue-500 ring-inset' : '' }}
                                {{ !$isValidDay ? 'bg-gray-50/70' : '' }}
                                {{ $isWeekend && $isValidDay && !$isToday ? 'bg-gray-50/30' : '' }}">
                                @if($isValidDay && $dayData)
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-xs font-semibold sm:text-sm
                                            {{ $isToday ? 'flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white sm:h-7 sm:w-7' : 'text-gray-700' }}">
                                            {{ $cellDay }}
                                        </span>
                                        @if($dayData['is_weekend'])
                                            <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[8px] font-medium text-gray-500 sm:text-[10px]">Akhir Pekan</span>
                                        @endif
                                    </div>

                                    <div class="mt-1 space-y-1 sm:mt-2">
                                        @if($dayData['is_holiday'])
                                            <div class="rounded-lg bg-amber-50 px-1.5 py-1 text-[9px] leading-tight text-amber-800 ring-1 ring-amber-100 sm:px-2 sm:text-xs">
                                                <span class="block truncate font-semibold">Libur</span>
                                                @if($dayData['holiday_type'] == 'approved')
                                                    <span class="mt-1 inline-flex rounded-full bg-emerald-100 px-1.5 py-0.5 text-[8px] font-medium text-emerald-700 sm:text-[10px]">Disetujui</span>
                                                @elseif($dayData['holiday_type'] == 'taken')
                                                    <span class="mt-1 inline-flex rounded-full bg-blue-100 px-1.5 py-0.5 text-[8px] font-medium text-blue-700 sm:text-[10px]">Diambil</span>
                                                @else
                                                    <span class="mt-1 inline-flex rounded-full bg-yellow-100 px-1.5 py-0.5 text-[8px] font-medium text-yellow-700 sm:text-[10px]">Terjadwal</span>
                                                @endif
                                                @if($dayData['holiday'] && property_exists($dayData['holiday'], 'reason') && $dayData['holiday']->reason)
                                                    <span class="mt-1 hidden truncate text-[10px] text-amber-600 sm:block">{{ Str::limit($dayData['holiday']->reason, 24) }}</span>
                                                @endif
                                            </div>
                                        @elseif($dayData['shift'])
                                            <div class="rounded-lg bg-emerald-50 px-1.5 py-1 text-[9px] leading-tight text-emerald-800 ring-1 ring-emerald-100 sm:px-2 sm:text-xs">
                                                <span class="block truncate font-semibold">{{ $dayData['shift']->name }}</span>
                                                <span class="mt-0.5 block text-[8px] font-medium text-emerald-600 sm:text-[10px]">
                                                    {{ date('H:i', strtotime($dayData['shift']->start_time)) }} - {{ date('H:i', strtotime($dayData['shift']->end_time)) }}
                                                </span>
                                                @if($dayData['employee_shift'] && $dayData['employee_shift']->is_recurring)
                                                    <span class="mt-1 inline-flex rounded-full bg-emerald-100 px-1.5 py-0.5 text-[8px] font-medium text-emerald-700 sm:text-[10px]">Rutin</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="rounded-lg border border-dashed border-red-200 bg-red-50 px-1.5 py-1 text-center text-[9px] font-medium text-red-500 sm:px-2 sm:text-xs">
                                                Kosong
                                            </div>
                                        @endif
                                    </div>

                                    @if($isToday)
                                        <div class="mt-1 sm:mt-2">
                                            <span class="rounded-full bg-blue-100 px-1.5 py-0.5 text-[8px] font-semibold text-blue-700 sm:px-2 sm:text-[10px]">Hari Ini</span>
                                        </div>
                                    @endif
                                @elseif($isValidDay && !$dayData)
                                    <div class="text-xs font-semibold text-gray-700 sm:text-sm">{{ $cellDay }}</div>
                                @endif
                            </div>
                        @endfor
                    @endfor
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 text-sm text-gray-500 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span class="font-medium text-gray-700">{{ $stats['working_days'] ?? 0 }} hari kerja bulan ini</span>
                    <span>{{ $stats['holidays'] ?? 0 }} libur owner/cuti, {{ $stats['weekends'] ?? 0 }} akhir pekan</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
