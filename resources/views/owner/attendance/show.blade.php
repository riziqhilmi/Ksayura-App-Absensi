<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Detail Absensi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-blue-600 to-emerald-500 rounded-2xl shadow-lg p-6 mb-6 text-white">
                <div class="flex flex-wrap items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Detail Absensi</p>
                        <h3 class="text-2xl font-bold">{{ date('d F Y', strtotime($attendance->date)) }}</h3>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ $attendance->employee->user->name ?? '-' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium
                            @if($attendance->status == 'present') bg-emerald-500/30 text-white
                            @elseif($attendance->status == 'late') bg-yellow-500/30 text-white
                            @elseif($attendance->status == 'half_day') bg-blue-500/30 text-white
                            @elseif($attendance->status == 'leave') bg-purple-500/30 text-white
                            @elseif($attendance->status == 'auto_checkout') bg-orange-500/30 text-white
                            @else bg-red-500/30 text-white @endif">
                            @if($attendance->status == 'present')
                                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                            @elseif($attendance->status == 'late')
                                <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>
                            @elseif($attendance->status == 'half_day')
                                <span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span>
                            @elseif($attendance->status == 'leave')
                                <span class="w-2 h-2 rounded-full bg-purple-400 mr-2"></span>
                            @elseif($attendance->status == 'auto_checkout')
                                <span class="w-2 h-2 rounded-full bg-orange-400 mr-2"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>
                            @endif
                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            @if($attendance->is_auto_checkout)
                                <span class="ml-1">🤖</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Info Karyawan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($attendance->employee->user->name ?? '', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800">{{ $attendance->employee->user->name ?? '-' }}</h4>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-1">
                            <span>📋 {{ $attendance->employee->employee_code ?? '-' }}</span>
                            <span>💼 {{ $attendance->employee->position ?? 'Staff' }}</span>
                            <span>📧 {{ $attendance->employee->user->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="ml-auto text-right">
                        <p class="text-sm text-gray-500">Shift</p>
                        <p class="font-semibold text-gray-800">{{ $attendance->shift ? $attendance->shift->name : '-' }}</p>
                        @if($attendance->shift)
                            <p class="text-xs text-gray-500">
                                {{ date('H:i', strtotime($attendance->shift->start_time)) }} - {{ date('H:i', strtotime($attendance->shift->end_time)) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Absensi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Timeline Absensi
                </h4>

                <div class="relative">
                    <!-- Garis vertikal -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <!-- Check In -->
                    <div class="relative pl-12 pb-8">
                        <div class="absolute left-0 top-1 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center border-2 border-emerald-500">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800">Check In</p>
                                    @if($attendance->check_in_time)
                                        <p class="text-2xl font-bold text-emerald-600">{{ date('H:i', strtotime($attendance->check_in_time)) }}</p>
                                        <p class="text-sm text-gray-500">{{ date('d F Y', strtotime($attendance->check_in_time)) }}</p>
                                    @else
                                        <p class="text-gray-400">Belum check in</p>
                                    @endif
                                </div>
                                @if($attendance->check_in_time && $attendance->latitude_in && $attendance->longitude_in)
                                    <button onclick="showLocation({{ $attendance->latitude_in }}, {{ $attendance->longitude_in }})" 
                                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition text-sm">
                                        📍 Lihat Lokasi
                                    </button>
                                @endif
                            </div>
                            @if($attendance->check_in_time && $attendance->latitude_in && $attendance->longitude_in)
                                <div class="mt-2 text-xs text-gray-500">
                                    <span>Lat: {{ $attendance->latitude_in }}</span>
                                    <span class="ml-3">Lng: {{ $attendance->longitude_in }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Keterlambatan -->
                    @php
                        $lateMinutes = 0;
                        $isLate = false;
                        if ($attendance->check_in_time && $attendance->shift) {
                            $checkIn = Carbon\Carbon::parse($attendance->check_in_time);
                            $shiftStart = Carbon\Carbon::parse($attendance->shift->start_time);
                            $shiftStart->setDate($checkIn->year, $checkIn->month, $checkIn->day);
                            $gracePeriod = $attendance->shift->grace_period ?? 15;
                            $lateThreshold = $shiftStart->copy()->addMinutes($gracePeriod);
                            if ($checkIn->greaterThan($lateThreshold)) {
                                $isLate = true;
                                $lateMinutes = $lateThreshold->diffInMinutes($checkIn);
                            }
                        }
                    @endphp

                    @if($attendance->check_in_time && $isLate)
                        <div class="relative pl-12 pb-8">
                            <div class="absolute left-0 top-1 w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center border-2 border-yellow-500">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-yellow-800">⚠️ Keterlambatan</p>
                                        <p class="text-2xl font-bold text-yellow-600">{{ $lateMinutes }} menit</p>
                                        <p class="text-sm text-yellow-600">
                                            Batas toleransi: {{ $attendance->shift->grace_period ?? 15 }} menit
                                        </p>
                                    </div>
                                    <div class="text-right text-sm text-yellow-700">
                                        <p>Shift mulai: {{ date('H:i', strtotime($attendance->shift->start_time)) }}</p>
                                        <p>Check in: {{ date('H:i', strtotime($attendance->check_in_time)) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Check Out -->
                    <div class="relative pl-12">
                        <div class="absolute left-0 top-1 w-8 h-8 rounded-full 
                            @if($attendance->check_out_time) bg-blue-100 border-2 border-blue-500
                            @else bg-gray-100 border-2 border-gray-300 @endif
                            flex items-center justify-center">
                            <svg class="w-4 h-4 
                                @if($attendance->check_out_time) text-blue-600
                                @else text-gray-400 @endif" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800">Check Out</p>
                                    @if($attendance->check_out_time)
                                        <p class="text-2xl font-bold text-blue-600">{{ date('H:i', strtotime($attendance->check_out_time)) }}</p>
                                        <p class="text-sm text-gray-500">{{ date('d F Y', strtotime($attendance->check_out_time)) }}</p>
                                        @if($attendance->is_auto_checkout)
                                            <p class="text-xs text-orange-500 mt-1">🤖 Auto Check Out</p>
                                        @endif
                                    @else
                                        <p class="text-gray-400">Belum check out</p>
                                    @endif
                                </div>
                                @if($attendance->check_out_time && $attendance->latitude_out && $attendance->longitude_out)
                                    <button onclick="showLocation({{ $attendance->latitude_out }}, {{ $attendance->longitude_out }})" 
                                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition text-sm">
                                        📍 Lihat Lokasi
                                    </button>
                                @endif
                            </div>
                            @if($attendance->check_out_time && $attendance->latitude_out && $attendance->longitude_out)
                                <div class="mt-2 text-xs text-gray-500">
                                    <span>Lat: {{ $attendance->latitude_out }}</span>
                                    <span class="ml-3">Lng: {{ $attendance->longitude_out }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Durasi Kerja -->
                    @if($attendance->check_in_time && $attendance->check_out_time)
                        @php
                            $checkIn = Carbon\Carbon::parse($attendance->check_in_time);
                            $checkOut = Carbon\Carbon::parse($attendance->check_out_time);
                            $durationMinutes = $checkIn->diffInMinutes($checkOut);
                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                        @endphp
                        <div class="relative pl-12 pt-8">
                            <div class="absolute left-0 top-9 w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center border-2 border-purple-500">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-purple-800">📊 Durasi Kerja</p>
                                        <p class="text-2xl font-bold text-purple-600">{{ $hours }} jam {{ $minutes }} menit</p>
                                    </div>
                                    <div class="text-right text-sm text-purple-700">
                                        <p>Total: {{ $durationMinutes }} menit</p>
                                        @if($durationMinutes < 240)
                                            <p class="text-yellow-600">⚠️ Kurang dari 4 jam</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Detail Informasi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Informasi Shift -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informasi Shift
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Nama Shift</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->shift ? $attendance->shift->name : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Jam Mulai</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->shift ? date('H:i', strtotime($attendance->shift->start_time)) : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Jam Selesai</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->shift ? date('H:i', strtotime($attendance->shift->end_time)) : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-500">Toleransi</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->shift ? $attendance->shift->grace_period . ' menit' : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status & Catatan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Status & Catatan
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Status</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($attendance->status == 'present') bg-emerald-100 text-emerald-800
                                @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                @elseif($attendance->status == 'half_day') bg-blue-100 text-blue-800
                                @elseif($attendance->status == 'leave') bg-purple-100 text-purple-800
                                @elseif($attendance->status == 'auto_checkout') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                        </div>
                        @if($attendance->status == 'late' && $lateMinutes > 0)
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Keterlambatan</span>
                                <span class="text-sm font-medium text-yellow-600">{{ $lateMinutes }} menit</span>
                            </div>
                        @endif
                        @if($attendance->is_auto_checkout)
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Auto Check Out</span>
                                <span class="text-sm font-medium text-orange-600">🤖 Ya</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Tanggal Dibuat</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->created_at->format('d F Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-500">Terakhir Update</span>
                            <span class="text-sm font-medium text-gray-800">{{ $attendance->updated_at->format('d F Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            @if($attendance->notes)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Catatan
                    </h4>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-800">{{ $attendance->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-wrap items-center justify-end space-x-3">
                    <a href="{{ route('owner.attendance.index') }}"
                        class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                        Kembali
                    </a>
                    <button onclick="openEditStatus({{ $attendance->id }})"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Status
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Status -->
    <div id="editStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Status Absensi</h3>
                <button onclick="closeEditStatus()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editStatusForm" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" id="attendanceId" name="attendance_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="editStatus" name="status" required 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="present">Hadir</option>
                        <option value="late">Terlambat</option>
                        <option value="half_day">Setengah Hari</option>
                        <option value="absent">Tidak Hadir</option>
                        <option value="leave">Cuti</option>
                        <option value="auto_checkout">Auto Check Out</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="editNotes" name="notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Tambahkan catatan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeEditStatus()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-emerald-500 text-white rounded-xl hover:from-blue-700 hover:to-emerald-600 transition shadow-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let editAttendanceId = null;

        function showLocation(lat, lng) {
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        }

        function openEditStatus(id) {
            editAttendanceId = id;
            document.getElementById('attendanceId').value = id;
            
            // Set current status
            const currentStatus = '{{ $attendance->status }}';
            document.getElementById('editStatus').value = currentStatus;
            document.getElementById('editNotes').value = '{{ $attendance->notes ?? '' }}';
            
            document.getElementById('editStatusModal').classList.remove('hidden');
            document.getElementById('editStatusModal').classList.add('flex');
        }

        function closeEditStatus() {
            document.getElementById('editStatusModal').classList.add('hidden');
            document.getElementById('editStatusModal').classList.remove('flex');
            editAttendanceId = null;
        }

        document.getElementById('editStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            const formData = new FormData(this);
            const data = {
                status: formData.get('status'),
                notes: formData.get('notes')
            };
            const id = document.getElementById('attendanceId').value;

            fetch(`/owner/attendance/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Gagal update status'));
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan';
                closeEditStatus();
            });
        });

        // Close modal on click outside
        document.getElementById('editStatusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditStatus();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditStatus();
            }
        });
    </script>
    @endpush
</x-app-layout>