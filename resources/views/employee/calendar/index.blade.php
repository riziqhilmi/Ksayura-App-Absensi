<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                Kalender Kerja Saya
            </h2>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>{{ $employee->user->name }}</span>
            </div>
        </div>
    </x-slot>

    @php
        $workingPercent = $stats['total_days'] > 0 ? ($stats['working_days'] / $stats['total_days']) * 100 : 0;
    @endphp

    <div class="bg-gray-50 py-6">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-col gap-5 bg-gradient-to-r from-blue-600 via-cyan-600 to-emerald-500 p-5 text-white sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <p class="text-sm font-medium text-white/80">Kalender Kerja</p>
                        <h3 class="mt-1 text-2xl font-bold">{{ $monthName }}</h3>
                        <p class="mt-1 text-sm text-white/80">{{ $employee->user->name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('employee.calendar.index', ['month' => $month - 1, 'year' => $year]) }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25"
                           title="Bulan sebelumnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ route('employee.calendar.index', ['month' => now()->month, 'year' => now()->year]) }}"
                           class="inline-flex h-10 items-center justify-center rounded-xl bg-white px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                            Hari Ini
                        </a>
                        <a href="{{ route('employee.calendar.index', ['month' => $month + 1, 'year' => $year]) }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25"
                           title="Bulan berikutnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-400">Total Hari</p>
                            <p class="mt-1 text-xl font-bold text-gray-800">{{ $stats['total_days'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-400">Hari Kerja</p>
                            <p class="mt-1 text-xl font-bold text-emerald-600">{{ $stats['working_days'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 h-1 w-full rounded-full bg-gray-100">
                        <div class="h-1 rounded-full bg-emerald-500 transition-all" style="width: {{ $workingPercent }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-400">Libur</p>
                            <p class="mt-1 text-xl font-bold text-amber-600">{{ $stats['holidays'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-400">Akhir Pekan</p>
                            <p class="mt-1 text-xl font-bold text-gray-500">{{ $stats['weekends'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                <span class="mr-1 text-sm font-semibold text-gray-700">Legenda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Shift
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Libur
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-medium text-orange-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span> Cuti
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
                                                @if($dayData['holiday'] && is_object($dayData['holiday']) && method_exists($dayData['holiday'], 'getTypeLabel'))
                                                    <span class="mt-0.5 hidden truncate text-[10px] text-amber-500 sm:block">{{ $dayData['holiday']->getTypeLabel() }}</span>
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
                    <span class="font-medium text-gray-700">{{ $stats['working_days'] }} hari kerja bulan ini</span>
                    <span>{{ $stats['holidays'] }} libur, {{ $stats['weekends'] }} akhir pekan</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>