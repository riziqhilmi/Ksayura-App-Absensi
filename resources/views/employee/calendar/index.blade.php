<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Kalender Kerja Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Info Card -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total Hari</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_days'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Hari Kerja</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $stats['working_days'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Libur</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $stats['holidays'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Weekend</p>
                            <p class="text-2xl font-bold text-gray-400">{{ $stats['weekends'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
<div class="flex flex-wrap items-center gap-4 mb-6 p-4 bg-white rounded-xl shadow-sm border border-gray-100">
    <span class="text-sm font-medium text-gray-700">Legenda:</span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-emerald-500 mr-1"></span> Shift Kerja
    </span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-purple-500 mr-1"></span> Libur Terjadwal
    </span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-orange-500 mr-1"></span> Cuti Disetujui ✅
    </span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-gray-200 mr-1 border border-gray-300"></span> Weekend
    </span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span> Hari Ini
    </span>
    <span class="inline-flex items-center text-xs">
        <span class="w-3 h-3 rounded-full bg-red-100 mr-1 border border-red-300"></span> Belum Diisi
    </span>
</div>

            <!-- Navigation -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('employee.calendar.index', ['month' => $month - 1, 'year' => $year]) }}" 
                       class="p-2 bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h3 class="text-xl font-bold text-gray-800">{{ $monthName }}</h3>
                    <a href="{{ route('employee.calendar.index', ['month' => $month + 1, 'year' => $year]) }}" 
                       class="p-2 bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('employee.calendar.index', ['month' => now()->month, 'year' => now()->year]) }}" 
                       class="px-4 py-2 text-sm bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition">
                        Hari Ini
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    {{ $employee->user->name }}
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Day Names -->
                <div class="grid grid-cols-7 bg-gray-50 border-b">
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Minggu</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Senin</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Selasa</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Rabu</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Kamis</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumat</div>
                    <div class="py-3 text-center text-xs font-medium text-gray-500 uppercase">Sabtu</div>
                </div>

                <!-- Calendar Body -->
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
                            @endphp

                            <div class="min-h-[100px] border-r border-b p-2 {{ $isToday ? 'bg-blue-50' : 'hover:bg-gray-50' }}">
                                @if($isValidDay && $dayData)
                                    <!-- Date Number -->
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium {{ $isToday ? 'text-blue-600 bg-blue-100 rounded-full w-7 h-7 flex items-center justify-center' : 'text-gray-700' }}">
                                            {{ $cellDay }}
                                        </span>
                                        @if($dayData['is_weekend'] && !$dayData['is_holiday'])
                                            <span class="text-xs text-gray-400">Weekend</span>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="space-y-1">
                                    @if($dayData['is_holiday'])
    <!-- Holiday -->
    <div class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-lg">
        <span class="font-medium">🎉 Libur</span>
        @if($dayData['holiday_type'] == 'approved')
            <span class="ml-1 text-[10px] bg-green-200 text-green-700 px-1.5 py-0.5 rounded-full"> Disetujui</span>
        @elseif($dayData['holiday_type'] == 'taken')
            <span class="ml-1 text-[10px] bg-blue-200 text-blue-700 px-1.5 py-0.5 rounded-full">📋 Diambil</span>
        @else
            <span class="ml-1 text-[10px] bg-yellow-200 text-yellow-700 px-1.5 py-0.5 rounded-full">⏳ Terjadwal</span>
        @endif
        @if($dayData['holiday'] && property_exists($dayData['holiday'], 'reason') && $dayData['holiday']->reason)
            <br>
            <span class="text-[10px] text-purple-600">{{ Str::limit($dayData['holiday']->reason, 20) }}</span>
        @endif
        @if($dayData['holiday'] && is_object($dayData['holiday']) && method_exists($dayData['holiday'], 'getTypeLabel'))
            <br>
            <span class="text-[10px] text-purple-500">{{ $dayData['holiday']->getTypeLabel() }}</span>
        @endif
    </div>
@elseif($dayData['shift'])
    <!-- Working Day with Shift -->
    <div class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-lg">
        <span class="font-medium">🕐 {{ $dayData['shift']->name }}</span>
        <br>
        <span class="text-[10px] text-emerald-600">
            {{ date('H:i', strtotime($dayData['shift']->start_time)) }} - {{ date('H:i', strtotime($dayData['shift']->end_time)) }}
        </span>
        @if($dayData['employee_shift'] && $dayData['employee_shift']->is_recurring)
            <span class="text-[10px] text-emerald-400 ml-1">(Berulang)</span>
        @endif
    </div>
@elseif($dayData['is_weekend'])
    <!-- Weekend -->
    <div class="bg-gray-100 text-gray-400 text-xs px-2 py-1 rounded-lg text-center">
        🌙 Libur
    </div>
@else
    <!-- Empty / No shift -->
    <div class="bg-red-50 text-red-400 text-xs px-2 py-1 rounded-lg text-center border border-dashed border-red-200">
        ⚠️ Belum Diisi
    </div>
@endif
                                    </div>

                                    <!-- Today indicator -->
                                    @if($isToday)
                                        <div class="mt-1">
                                            <span class="text-[10px] font-medium text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Hari Ini</span>
                                        </div>
                                    @endif

                                @elseif($isValidDay && !$dayData)
                                    <!-- Fallback if no data -->
                                    <div class="text-sm font-medium text-gray-700">{{ $cellDay }}</div>
                                    <div class="text-xs text-gray-300 mt-2">-</div>
                                @endif
                            </div>
                        @endfor
                    @endfor
                </div>
            </div>

            <!-- Info Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>💡 Klik pada tanggal untuk melihat detail shift atau libur Anda</p>
            </div>
        </div>
    </div>
</x-app-layout>