<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Kalender Hari Libur Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">Total Libur</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">Terjadwal</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['scheduled'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">Diambil</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['taken'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">Akan Datang</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['upcoming'] }}</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('owner.employee-holidays.calendar', ['month' => $month - 1, 'year' => $year]) }}" 
                       class="p-2 bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h3 class="text-xl font-bold text-gray-800">{{ $monthName }}</h3>
                    <a href="{{ route('owner.employee-holidays.calendar', ['month' => $month + 1, 'year' => $year]) }}" 
                       class="p-2 bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('owner.employee-holidays.calendar', ['month' => now()->month, 'year' => now()->year]) }}" 
                       class="px-4 py-2 text-sm bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition">
                        Hari Ini
                    </a>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('owner.employee-holidays.index') }}" 
                       class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition">
                        Tampilan List
                    </a>
                    <a href="{{ route('owner.employee-holidays.create') }}" 
                       class="px-4 py-2 text-sm bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition">
                        + Tambah Libur
                    </a>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap gap-4 mb-6 p-4 bg-white rounded-xl shadow-sm border border-gray-100">
                <span class="text-sm font-medium text-gray-700">Legenda:</span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-1"></span> Cuti Tahunan
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span> Cuti Sakit
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span> Cuti Pribadi
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-purple-500 mr-1"></span> Libur Perusahaan
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-gray-500 mr-1"></span> Lainnya
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full bg-green-200 mr-1"></span> Hadir
                </span>
                <span class="inline-flex items-center text-sm">
                    <span class="w-3 h-3 rounded-full border border-dashed border-gray-400 mr-1"></span> Weekend
                </span>
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
                        $currentDay = 1;
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
                                $isToday = $dateStr == $today;
                                $isWeekend = $dateObj && $dateObj->isWeekend();
                            @endphp

                            <div class="min-h-[140px] border-r border-b p-2 {{ $isToday ? 'bg-blue-50' : ($isWeekend ? 'bg-gray-50' : 'hover:bg-gray-50') }}">
                                @if($isValidDay)
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium {{ $isToday ? 'text-blue-600 bg-blue-100 rounded-full w-7 h-7 flex items-center justify-center' : ($isWeekend ? 'text-red-400' : 'text-gray-700') }}">
                                            {{ $cellDay }}
                                        </span>
                                        @if($isWeekend)
                                            <span class="text-xs text-red-300">Weekend</span>
                                        @endif
                                    </div>

                                    <!-- Employee Holidays for this day -->
                                    <div class="space-y-1 max-h-[100px] overflow-y-auto">
                                        @foreach($employees as $employee)
                                            @php
                                                $holiday = null;
                                                if (isset($holidaysByEmployee[$employee->id])) {
                                                    foreach ($holidaysByEmployee[$employee->id] as $h) {
                                                        if ($h->date->format('Y-m-d') == $dateStr) {
                                                            $holiday = $h;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if($holiday)
                                                <div class="text-xs px-2 py-1 rounded-full truncate cursor-pointer 
                                                    @if($holiday->type == 'annual') bg-yellow-100 text-yellow-800
                                                    @elseif($holiday->type == 'sick') bg-red-100 text-red-800
                                                    @elseif($holiday->type == 'personal') bg-blue-100 text-blue-800
                                                    @elseif($holiday->type == 'company') bg-purple-100 text-purple-800
                                                    @else bg-gray-100 text-gray-800 @endif
                                                    hover:opacity-80 transition relative group"
                                                    title="{{ $employee->user->name }}: {{ $holiday->reason ?? $holiday->getTypeLabel() }}">
                                                    <span class="font-medium">{{ substr($employee->user->name, 0, 2) }}</span>
                                                    <span class="ml-1">{{ substr($holiday->getTypeLabel(), 0, 2) }}</span>
                                                    @if($holiday->status == 'taken')
                                                        <span class="text-xs ml-1 text-emerald-600">✓</span>
                                                    @endif
                                                    <!-- Tooltip -->
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-800 text-white text-xs rounded whitespace-nowrap hidden group-hover:block z-10">
                                                        {{ $employee->user->name }}<br>
                                                        {{ $holiday->getTypeLabel() }}<br>
                                                        {{ $holiday->reason ?? 'Tidak ada alasan' }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        <!-- Tombol Tambah Libur -->
                                        <button type="button" onclick="openAddHoliday('{{ $dateStr }}')"
                                                class="w-full rounded px-1 py-0.5 text-center text-xs text-gray-400 transition hover:bg-purple-50 hover:text-purple-600">
                                            + Tambah
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endfor
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Libur -->
    <div id="addHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Hari Libur</h3>
                <button onclick="closeAddHoliday()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4 p-3 bg-blue-50 rounded-xl">
                <p class="text-sm text-blue-700">
                    <span class="font-semibold">Tanggal:</span> 
                    <span id="selectedDateDisplay" class="font-medium"></span>
                </p>
            </div>

            <form id="addHolidayForm" class="space-y-4">
                @csrf
                <input type="hidden" id="holidayDate" name="date">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan <span class="text-red-500">*</span></label>
                    <select id="holidayEmployee" name="employee_id" required 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->user->name }} ({{ $employee->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Libur <span class="text-red-500">*</span></label>
                    <select id="holidayType" name="type" required 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="annual">Cuti Tahunan</option>
                        <option value="sick">Cuti Sakit</option>
                        <option value="personal">Cuti Pribadi</option>
                        <option value="company">Libur Perusahaan</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                    <input type="text" id="holidayReason" name="reason" 
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Masukkan alasan libur">
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeAddHoliday()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition shadow-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let selectedDate = '';

        function openAddHoliday(date) {
            selectedDate = date;
            document.getElementById('holidayDate').value = date;
            
            // Format date for display
            const dateObj = new Date(`${date}T00:00:00`);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('selectedDateDisplay').textContent = dateObj.toLocaleDateString('id-ID', options);
            
            document.getElementById('addHolidayModal').classList.remove('hidden');
            document.getElementById('addHolidayModal').classList.add('flex');
        }

        function closeAddHoliday() {
            document.getElementById('addHolidayModal').classList.add('hidden');
            document.getElementById('addHolidayModal').classList.remove('flex');
            document.getElementById('addHolidayForm').reset();
        }

        document.getElementById('addHolidayForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            
            const formData = new FormData(this);
            
            fetch('{{ route("owner.employee-holidays.store-from-calendar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json().catch(() => ({
                    success: false,
                    message: 'Respons server tidak valid.'
                }));

                if (!response.ok && !data.message) {
                    data.message = 'Gagal menyimpan hari libur.';
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan';
            });
        });

        // Close modal on click outside
        document.getElementById('addHolidayModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddHoliday();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddHoliday();
            }
        });
    </script>
    @endpush
</x-app-layout>
