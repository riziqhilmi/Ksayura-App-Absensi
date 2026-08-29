<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Absensi Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Today's Attendance Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-emerald-500 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Absensi Hari Ini - {{ now()->format('l, d F Y') }}
                    </h3>
                </div>
                <div class="p-6">
                    <!-- Location Status Banner -->
                    <div id="location-banner" class="mb-6 p-4 rounded-xl hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div id="location-icon" class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p id="location-status-text" class="text-sm font-medium text-gray-800">Mengecek lokasi...</p>
                                    <p id="location-detail-text" class="text-xs text-gray-500">Mohon tunggu sebentar</p>
                                </div>
                            </div>
                            <button id="enable-location-btn" onclick="requestLocationPermission()" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition duration-200 hidden">
                                Aktifkan Lokasi
                            </button>
                        </div>
                    </div>

                    <!-- Shift Info -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500">Shift</p>
                            <p class="font-semibold text-gray-800" id="shift-name">
                                {{ $todayShift ? $todayShift->name : 'Anda belum diberi shift' }}
                            </p>
                            <p class="text-sm text-gray-500" id="shift-time">
                                @if($todayShift)
                                    {{ date('H:i', strtotime($todayShift->start_time)) }} - {{ date('H:i', strtotime($todayShift->end_time)) }}
                                    <span class="text-xs text-yellow-600 ml-2">(Toleransi {{ $todayShift->grace_period }} menit)</span>
                                @else
                                    Silakan hubungi owner/admin
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500">Status</p>
                            <div id="attendance-status-display">
                                <div class="animate-pulse">
                                    <div class="h-6 w-24 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                            <div id="attendance-detail-info" class="mt-1 text-xs text-gray-500"></div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500">Durasi Kerja</p>
                            <p class="font-semibold text-gray-800" id="work-duration">-</p>
                            <p class="text-sm text-gray-500" id="work-time">-</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500">Keterlambatan</p>
                            <p class="font-semibold text-gray-800" id="late-display">-</p>
                            <p class="text-sm text-gray-500" id="late-time">-</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500">Jam Masuk</p>
                            <p class="font-semibold text-gray-800" id="checkin-window">-</p>
                            <p class="text-sm text-gray-500" id="checkin-window-time">-</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div id="attendance-actions" class="flex flex-wrap gap-4">
                        <button id="checkin-btn" onclick="handleCheckIn()" 
                                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-blue-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-blue-700 transition duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Check In
                            </span>
                        </button>
                        <button id="checkout-btn" onclick="handleCheckOut()" 
                                class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white font-medium rounded-xl hover:from-orange-600 hover:to-red-700 transition duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Check Out
                            </span>
                        </button>
                        <div id="attendance-info" class="flex items-center text-sm text-gray-500"></div>
                    </div>

                    <!-- Location Warning -->
                    <div id="location-warning" class="mt-4 p-3 bg-yellow-50 rounded-xl hidden">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Perhatian!</p>
                                <p class="text-sm text-yellow-700">Pastikan Anda berada di lokasi kantor untuk melakukan absensi.</p>
                                <p class="text-xs text-yellow-600 mt-1">Lokasi kantor: {{ $officeLocation['address'] ?? 'Belum diatur' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Hadir</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['present'] }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? ($stats['present'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['late'] }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? ($stats['late'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Tidak Hadir</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['absent'] }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? ($stats['absent'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Cuti / ½ Hari</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['leave'] + $stats['half_day'] }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? (($stats['leave'] + $stats['half_day']) / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Auto CO</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['auto_checkout'] ?? 0 }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-orange-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? (($stats['auto_checkout'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- History -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-wrap gap-2">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Riwayat Absensi
                    </h3>
                    <div class="flex items-center space-x-2">
                        <select id="month-filter" onchange="window.location.href='?month='+this.value+'&year='+document.getElementById('year-filter').value" 
                                class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endfor
                        </select>
                        <select id="year-filter" onchange="window.location.href='?month='+document.getElementById('month-filter').value+'&year='+this.value" 
                                class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                            @for($y = date('Y')-2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($attendances as $attendance)
                                @php
                                    $lateMinutes = 0;
                                    if ($attendance->check_in_time && $attendance->shift) {
                                        $checkIn = Carbon\Carbon::parse($attendance->check_in_time);
                                        $shiftStart = Carbon\Carbon::parse($attendance->shift->start_time);
                                        $shiftStart->setDate($checkIn->year, $checkIn->month, $checkIn->day);
                                        $gracePeriod = $attendance->shift->grace_period ?? 15;
                                        $lateThreshold = $shiftStart->copy()->addMinutes($gracePeriod);
                                        if ($checkIn->greaterThan($lateThreshold)) {
                                            $lateMinutes = $lateThreshold->diffInMinutes($checkIn);
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ date('d/m/Y', strtotime($attendance->date)) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $attendance->shift ? $attendance->shift->name : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($attendance->check_in_time)
                                            <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($attendance->check_in_time)) }}</span>
                                            @if($attendance->latitude_in && $attendance->longitude_in)
                                                <br>
                                                <button onclick="showLocation({{ $attendance->latitude_in }}, {{ $attendance->longitude_in }})" 
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                    📍 Lihat
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($attendance->check_out_time)
                                            <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($attendance->check_out_time)) }}</span>
                                            @if($attendance->is_auto_checkout)
                                                <span class="ml-1 text-xs text-orange-500">🤖</span>
                                            @endif
                                            @if($attendance->latitude_out && $attendance->longitude_out)
                                                <br>
                                                <button onclick="showLocation({{ $attendance->latitude_out }}, {{ $attendance->longitude_out }})" 
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                    📍 Lihat
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if($attendance->check_in_time && $attendance->check_out_time)
                                            @php
                                                $diff = Carbon\Carbon::parse($attendance->check_in_time)->diffInMinutes(Carbon\Carbon::parse($attendance->check_out_time));
                                                $hours = floor($diff / 60);
                                                $minutes = $diff % 60;
                                            @endphp
                                            <span class="font-medium">{{ $hours }}j {{ $minutes }}m</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($attendance->status == 'late' && $lateMinutes > 0)
                                            <span class="text-sm font-medium text-yellow-600">{{ $lateMinutes }} menit</span>
                                        @elseif($attendance->status == 'late')
                                            <span class="text-sm text-yellow-600">Terlambat</span>
                                        @elseif($attendance->status == 'present')
                                            <span class="text-sm text-emerald-600">Tepat waktu</span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($attendance->status == 'present') bg-emerald-100 text-emerald-800
                                            @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                            @elseif($attendance->status == 'half_day') bg-blue-100 text-blue-800
                                            @elseif($attendance->status == 'leave') bg-purple-100 text-purple-800
                                            @elseif($attendance->status == 'auto_checkout') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($attendance->status == 'present')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                            @elseif($attendance->status == 'late')
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1"></span>
                                            @elseif($attendance->status == 'half_day')
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1"></span>
                                            @elseif($attendance->status == 'leave')
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1"></span>
                                            @elseif($attendance->status == 'auto_checkout')
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1"></span>
                                                🤖
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1"></span>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                        </span>
                                        @if($attendance->is_auto_checkout)
                                            <span class="text-xs text-orange-500 block mt-0.5">Auto Check Out</span>
                                        @endif
                                        @if($attendance->status == 'late' && $lateMinutes > 0)
                                            <span class="text-xs text-yellow-500 block mt-0.5">+{{ $lateMinutes }} menit</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <p class="text-lg font-medium">Belum ada riwayat absensi</p>
                                        <p class="text-sm">Mulai absensi hari ini dengan melakukan Check In</p>
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
        // Define functions globally
        window.checkIn = checkIn;
        window.checkOut = checkOut;
        window.showLocation = showLocation;
        window.requestLocationPermission = requestLocationPermission;
        window.handleCheckIn = handleCheckIn;
        window.handleCheckOut = handleCheckOut;

        let todayStatus = {};
        let locationPermissionGranted = false;
        let lastLocation = null;

        // Show location on Google Maps
        function showLocation(lat, lng) {
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        }

        // Check if geolocation is available
        function isGeolocationAvailable() {
            return 'geolocation' in navigator;
        }

        // Request location permission
        function requestLocationPermission() {
            const banner = document.getElementById('location-banner');
            const statusText = document.getElementById('location-status-text');
            const detailText = document.getElementById('location-detail-text');
            const icon = document.getElementById('location-icon');
            const enableBtn = document.getElementById('enable-location-btn');

            if (!isGeolocationAvailable()) {
                statusText.textContent = '⚠️ Browser tidak mendukung geolokasi';
                detailText.textContent = 'Gunakan browser modern seperti Chrome atau Firefox';
                icon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center';
                return;
            }

            statusText.textContent = '⏳ Meminta akses lokasi...';
            detailText.textContent = 'Mohon izinkan akses lokasi di browser';
            enableBtn.classList.add('hidden');

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    locationPermissionGranted = true;
                    lastLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    
                    statusText.textContent = '✅ Lokasi aktif';
                    detailText.textContent = `Latitude: ${position.coords.latitude.toFixed(6)}, Longitude: ${position.coords.longitude.toFixed(6)}`;
                    icon.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center';
                    banner.className = 'mb-6 p-4 rounded-xl bg-green-50 border border-green-200';
                    
                    checkLocationProximity(position.coords.latitude, position.coords.longitude);
                },
                function(error) {
                    console.error('Location error:', error);
                    let errorMessage = '';
                    let suggestion = '';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = '⚠️ Akses lokasi ditolak';
                            suggestion = 'Klik tombol "Aktifkan Lokasi" di bawah atau izinkan di pengaturan browser';
                            enableBtn.classList.remove('hidden');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = '⚠️ Lokasi tidak tersedia';
                            suggestion = 'Pastikan GPS Anda aktif dan coba lagi';
                            break;
                        case error.TIMEOUT:
                            errorMessage = '⏱️ Timeout mendapatkan lokasi';
                            suggestion = 'Coba refresh halaman atau periksa koneksi internet';
                            break;
                        default:
                            errorMessage = '❌ Gagal mendapatkan lokasi';
                            suggestion = 'Coba refresh halaman';
                    }
                    
                    statusText.textContent = errorMessage;
                    detailText.textContent = suggestion;
                    icon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center';
                    banner.className = 'mb-6 p-4 rounded-xl bg-red-50 border border-red-200';
                    
                    document.getElementById('checkin-btn').disabled = true;
                    document.getElementById('checkout-btn').disabled = true;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Check location proximity to office
        function checkLocationProximity(lat, lng) {
            const locationWarning = document.getElementById('location-warning');
            const infoDiv = document.getElementById('attendance-info');
            
            fetch('{{ route("owner.settings.validate-location") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.is_within_radius) {
                    locationWarning.classList.remove('hidden');
                    if (infoDiv) {
                        infoDiv.textContent = `⚠️ Jarak Anda ${data.distance} meter dari kantor (Maks: ${data.radius} meter)`;
                        infoDiv.className = 'flex items-center text-sm text-red-600 font-medium';
                    }
                } else {
                    locationWarning.classList.add('hidden');
                    if (infoDiv) {
                        infoDiv.textContent = `✅ Dalam radius kantor (${data.distance} meter)`;
                        infoDiv.className = 'flex items-center text-sm text-green-600 font-medium';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking location:', error);
            });
        }

        // Get user's location with permission check
        function getLocation() {
            return new Promise((resolve, reject) => {
                if (!isGeolocationAvailable()) {
                    reject('Browser tidak mendukung geolokasi');
                    return;
                }

                if (!locationPermissionGranted) {
                    reject('Izin lokasi belum diberikan. Klik "Aktifkan Lokasi" terlebih dahulu.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    position => resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    }),
                    error => reject('Gagal mendapatkan lokasi: ' + error.message),
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        // Check today's status
        function checkTodayStatus() {
            const statusDisplay = document.getElementById('attendance-status-display');
            const detailInfo = document.getElementById('attendance-detail-info');
            const checkinBtn = document.getElementById('checkin-btn');
            const checkoutBtn = document.getElementById('checkout-btn');
            const workDuration = document.getElementById('work-duration');
            const workTime = document.getElementById('work-time');
            const lateDisplay = document.getElementById('late-display');
            const lateTime = document.getElementById('late-time');
            const checkinWindow = document.getElementById('checkin-window');
            const checkinWindowTime = document.getElementById('checkin-window-time');
            const infoDiv = document.getElementById('attendance-info');

            if (!statusDisplay) return;

            fetch('{{ route("employee.attendance.today-status") }}')
                .then(response => response.json())
                .then(data => {
                    todayStatus = data;

                    // Update shift info
                    if (data.shift) {
                        const shiftNameEl = document.getElementById('shift-name');
                        const shiftTimeEl = document.getElementById('shift-time');
                        if (shiftNameEl) shiftNameEl.textContent = data.shift.name;
                        if (shiftTimeEl) shiftTimeEl.textContent = 
                            data.shift.start_time + ' - ' + data.shift.end_time + 
                            ' (Toleransi ' + data.shift.grace_period + ' menit)';
                    } else if (data.status === 'no_shift') {
                        const shiftNameEl = document.getElementById('shift-name');
                        const shiftTimeEl = document.getElementById('shift-time');
                        if (shiftNameEl) {
                            shiftNameEl.textContent = 'Anda belum diberi shift';
                            shiftNameEl.className = 'font-semibold text-red-600';
                        }
                        if (shiftTimeEl) {
                            shiftTimeEl.textContent = 'Silakan hubungi owner/admin';
                            shiftTimeEl.className = 'text-sm text-red-500';
                        }
                    }

                    // Update jam masuk info
                    if (data.can_check_in !== undefined) {
                        if (data.can_check_in === false && data.available_from) {
                            checkinWindow.textContent = 'Belum Dibuka';
                            checkinWindow.className = 'font-semibold text-yellow-600';
                            checkinWindowTime.textContent = 'Mulai check in ' + data.available_from;
                        } else if (data.can_check_in === true) {
                            checkinWindow.textContent = 'Sudah Dibuka';
                            checkinWindow.className = 'font-semibold text-emerald-600';
                            checkinWindowTime.textContent = data.shift_start_time
                                ? 'Shift mulai ' + data.shift_start_time
                                : 'Akses check in sudah dibuka';
                        } else {
                            checkinWindow.textContent = '-';
                            checkinWindow.className = 'font-semibold text-gray-800';
                            checkinWindowTime.textContent = '-';
                        }
                    }

                    // === NO SHIFT ===
                    if (data.status === 'no_shift') {
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                                Tidak Bisa Absen
                            </span>
                        `;
                        if (detailInfo) {
                            detailInfo.textContent = data.message || 'Anda belum diberi shift';
                            detailInfo.className = 'mt-1 text-xs text-red-600';
                        }
                        workDuration.textContent = '-';
                        workTime.textContent = 'Tidak ada shift';
                        lateDisplay.textContent = '-';
                        lateTime.textContent = '-';
                        checkinWindow.textContent = '-';
                        checkinWindowTime.textContent = 'Jam masuk belum tersedia';
                        infoDiv.textContent = data.message || 'Anda belum diberi shift. Silakan hubungi owner/admin.';
                        infoDiv.className = 'flex items-center text-sm text-red-600 font-medium';
                        checkinBtn.disabled = true;
                        checkoutBtn.disabled = true;

                    // === HOLIDAY ===
                    } else if (data.status === 'holiday') {
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
                                <span class="w-2 h-2 rounded-full bg-purple-500 mr-2"></span>
                                Libur
                            </span>
                        `;
                        if (detailInfo) {
                            detailInfo.textContent = data.holiday?.reason || 'Hari libur';
                            detailInfo.className = 'mt-1 text-xs text-purple-600';
                        }
                        workDuration.textContent = '-';
                        workTime.textContent = data.holiday?.type || 'Hari libur';
                        lateDisplay.textContent = '-';
                        lateTime.textContent = '-';
                        infoDiv.textContent = 'Hari ini adalah jadwal libur Anda';
                        infoDiv.className = 'flex items-center text-sm text-purple-600 font-medium';
                        checkinBtn.disabled = true;
                        checkoutBtn.disabled = true;
                    
                    // === PAST CHECK IN (Melewati batas waktu) ===
                    } else if (data.status === 'past_check_in') {
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                                Tidak Hadir
                            </span>
                        `;
                        if (detailInfo) {
                            detailInfo.textContent = 'Batas check in: ' + data.max_check_in_time;
                            detailInfo.className = 'mt-1 text-xs text-red-600';
                        }
                        workDuration.textContent = '-';
                        workTime.textContent = 'Melewati batas waktu';
                        lateDisplay.textContent = '-';
                        lateTime.textContent = '-';
                        infoDiv.textContent = 'Anda sudah melewati batas waktu check in (maksimal 2 jam setelah shift berakhir)';
                        infoDiv.className = 'flex items-center text-sm text-red-600 font-medium';
                        checkinBtn.disabled = true;
                        checkoutBtn.disabled = true;
                    
                    // === NOT STARTED ===
                    } else if (data.status === 'not_started') {
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                                Belum Absen
                            </span>
                        `;
                        if (detailInfo) {
                            if (data.can_check_in === false) {
                                detailInfo.textContent = 'Check in mulai pukul ' + data.available_from;
                                detailInfo.className = 'mt-1 text-xs text-yellow-600';
                            } else {
                                detailInfo.textContent = 'Keadaan sekarang: belum check in';
                                detailInfo.className = 'mt-1 text-xs text-gray-500';
                            }
                        }
                        workDuration.textContent = '-';
                        workTime.textContent = 'Belum mulai';
                        lateDisplay.textContent = '-';
                        lateTime.textContent = '-';
                        if (infoDiv && !infoDiv.textContent.includes('Jarak')) {
                            if (data.can_check_in === false) {
                                infoDiv.textContent = 'Belum waktunya check in. Mulai pukul ' + data.available_from;
                                infoDiv.className = 'flex items-center text-sm text-yellow-600 font-medium';
                            } else {
                                infoDiv.textContent = 'Keadaan sekarang: belum check in';
                                infoDiv.className = 'flex items-center text-sm text-gray-500';
                            }
                        }
                        checkinBtn.disabled = !locationPermissionGranted || data.can_check_in === false;
                        checkoutBtn.disabled = true;
                    
                    // === CHECKED IN (Belum Check Out) ===
                    } else if (data.checked_in && !data.checked_out) {
                        const statusMap = {
                            'present': 'Hadir',
                            'late': 'Terlambat',
                            'half_day': 'Setengah Hari'
                        };
                        
                        let statusDisplayText = statusMap[data.status] || data.status;
                        let statusExtra = '';
                        let statusIcon = 'bg-emerald-500';
                        let bgColor = 'bg-emerald-100 text-emerald-700';
                        
                        if (data.status === 'late' && data.late_minutes > 0) {
                            statusExtra = ' (' + data.late_minutes + ' menit)';
                            statusIcon = 'bg-yellow-500';
                            bgColor = 'bg-yellow-100 text-yellow-700';
                        }
                        
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${bgColor}">
                                <span class="w-2 h-2 rounded-full ${statusIcon} mr-2 animate-pulse"></span>
                                ${statusDisplayText}${statusExtra}
                            </span>
                        `;
                        
                        if (detailInfo) {
                            detailInfo.textContent = 'Check in: ' + data.check_in_time;
                            if (data.status === 'late' && data.late_minutes > 0) {
                                detailInfo.textContent += ' | Terlambat ' + data.late_minutes + ' menit';
                            }
                            detailInfo.className = 'mt-1 text-xs text-gray-500';
                        }
                        
                        workDuration.textContent = 'Sedang Berlangsung';
                        workTime.textContent = 'Mulai: ' + data.check_in_time;
                        
                        if (data.status === 'late' && data.late_minutes > 0) {
                            lateDisplay.textContent = data.late_minutes + ' menit';
                            lateTime.textContent = 'Terlambat dari shift';
                            lateDisplay.className = 'font-semibold text-yellow-600';
                        } else {
                            lateDisplay.textContent = 'Tepat waktu';
                            lateTime.textContent = '✅';
                            lateDisplay.className = 'font-semibold text-emerald-600';
                        }
                        
                        if (infoDiv && !infoDiv.textContent.includes('Jarak')) {
                            infoDiv.textContent = 'Status: ' + (statusMap[data.status] || data.status);
                            if (data.status === 'late' && data.late_minutes > 0) {
                                infoDiv.textContent += ' (Terlambat ' + data.late_minutes + ' menit)';
                            }
                            infoDiv.className = 'flex items-center text-sm text-gray-500';
                        }
                        checkinBtn.disabled = true;
                        checkoutBtn.disabled = !locationPermissionGranted;
                    
                    // === CHECKED OUT ===
                    } else if (data.checked_out) {
                        const statusMap = {
                            'present': 'Hadir',
                            'late': 'Terlambat',
                            'half_day': 'Setengah Hari',
                            'absent': 'Tidak Hadir',
                            'leave': 'Cuti',
                            'auto_checkout': 'Auto Check Out'
                        };
                        
                        let statusDisplayText = statusMap[data.status] || data.status;
                        let statusIcon = 'bg-blue-500';
                        let bgColor = 'bg-blue-100 text-blue-700';
                        let detailExtra = '';
                        
                        if (data.status === 'auto_checkout') {
                            statusIcon = 'bg-orange-500';
                            bgColor = 'bg-orange-100 text-orange-700';
                            detailExtra = ' 🤖 Auto Check Out';
                        } else if (data.status === 'half_day') {
                            statusIcon = 'bg-purple-500';
                            bgColor = 'bg-purple-100 text-purple-700';
                        }
                        
                        statusDisplay.innerHTML = `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${bgColor}">
                                <span class="w-2 h-2 rounded-full ${statusIcon} mr-2"></span>
                                ${statusDisplayText}
                            </span>
                        `;
                        
                        if (detailInfo) {
                            let detailText = 'In: ' + data.check_in_time + ' | Out: ' + data.check_out_time;
                            if (data.is_auto_checkout) {
                                detailText += ' | 🤖 Auto Check Out';
                            }
                            detailInfo.textContent = detailText;
                            detailInfo.className = 'mt-1 text-xs text-gray-500';
                        }
                        
                        workDuration.textContent = data.work_duration_text || '-';
                        workTime.textContent = 'Selesai: ' + data.check_out_time;
                        
                        if (data.status === 'late') {
                            lateDisplay.textContent = data.late_minutes + ' menit';
                            lateTime.textContent = 'Terlambat';
                            lateDisplay.className = 'font-semibold text-yellow-600';
                        } else {
                            lateDisplay.textContent = '-';
                            lateTime.textContent = '-';
                        }
                        
                        if (infoDiv && !infoDiv.textContent.includes('Jarak')) {
                            infoDiv.textContent = 'Status: ' + (statusMap[data.status] || data.status);
                            if (data.is_auto_checkout) {
                                infoDiv.textContent += ' (Auto Check Out)';
                            }
                            infoDiv.className = 'flex items-center text-sm text-gray-500';
                        }
                        checkinBtn.disabled = true;
                        checkoutBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDisplay.innerHTML = `
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                            <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                            Error
                        </span>
                    `;
                    if (infoDiv) infoDiv.textContent = 'Gagal memuat data';
                });
        }

        // Handle Check In
        function handleCheckIn() {
            if (todayStatus?.status === 'no_shift') {
                alert('⚠️ Anda belum diberi shift. Silakan hubungi owner/admin.');
                return;
            }

            if (todayStatus?.status === 'holiday') {
                alert('⚠️ Hari ini adalah jadwal libur Anda. Check in tidak dapat dilakukan.');
                return;
            }

            if (todayStatus?.status === 'past_check_in') {
                alert('⚠️ Anda sudah melewati batas waktu check in.');
                return;
            }

            if (todayStatus?.can_check_in === false) {
                alert(`⏰ Anda masih belum bisa absen.\n\nCheck in dapat dilakukan mulai pukul ${todayStatus.available_from}.`);
                return;
            }

            if (!locationPermissionGranted) {
                alert('⚠️ Mohon aktifkan lokasi terlebih dahulu!\n\nKlik tombol "Aktifkan Lokasi" di atas.');
                requestLocationPermission();
                return;
            }
            checkIn();
        }

        // Handle Check Out
        function handleCheckOut() {
            if (!locationPermissionGranted) {
                alert('⚠️ Mohon aktifkan lokasi terlebih dahulu!\n\nKlik tombol "Aktifkan Lokasi" di atas.');
                requestLocationPermission();
                return;
            }
            checkOut();
        }

        // Check In
        function checkIn() {
            if (!confirm('Anda yakin ingin melakukan Check In?\n\nPastikan Anda berada di lokasi kantor.')) return;

            const btn = document.getElementById('checkin-btn');
            if (!btn) return;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...</span>';

            getLocation()
                .then(location => {
                    return fetch('{{ route("employee.attendance.check-in") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(location)
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusText = data.status === 'present' ? 'Hadir ✅' : 'Terlambat ⚠️';
                        let msg = '✅ Check In Berhasil!\n\nStatus: ' + statusText;
                        if (data.status === 'late' && data.late_minutes > 0) {
                            msg += '\n\n⏰ Anda terlambat ' + data.late_minutes + ' menit.';
                        }
                        alert(msg);
                        checkTodayStatus();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        let errorMsg = data.error || 'Gagal check in';
                        if (data.is_holiday) {
                            errorMsg += '\n\n📅 Hari ini adalah jadwal libur Anda.';
                        }
                        if (data.available_from) {
                            errorMsg += '\n\n⏰ Check in dapat dilakukan mulai pukul ' + data.available_from;
                        }
                        if (data.distance !== undefined) {
                            errorMsg += '\n\n📍 Jarak Anda: ' + data.distance + ' meter dari kantor';
                            errorMsg += '\n📏 Maksimal jarak: ' + data.max_distance + ' meter';
                            errorMsg += '\n\n💡 Tip: Pastikan GPS Anda aktif dan berada di sekitar kantor.';
                        }
                        alert('❌ ' + errorMsg);
                        btn.disabled = false;
                        btn.innerHTML = '<span class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>Check In</span>';
                    }
                })
                .catch(error => {
                    alert('❌ Gagal check in: ' + error);
                    btn.disabled = false;
                    btn.innerHTML = '<span class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>Check In</span>';
                });
        }

        // Check Out
        function checkOut() {
            if (!confirm('Anda yakin ingin melakukan Check Out?\n\nPastikan Anda berada di lokasi kantor.')) return;

            const btn = document.getElementById('checkout-btn');
            if (!btn) return;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...</span>';

            getLocation()
                .then(location => {
                    return fetch('{{ route("employee.attendance.check-out") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(location)
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Check Out Berhasil!\n\n📊 Durasi Kerja: ' + data.work_duration_text);
                        checkTodayStatus();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        let errorMsg = data.error || 'Gagal check out';
                        if (data.distance !== undefined) {
                            errorMsg += '\n\n📍 Jarak Anda: ' + data.distance + ' meter dari kantor';
                            errorMsg += '\n📏 Maksimal jarak: ' + data.max_distance + ' meter';
                            errorMsg += '\n\n💡 Tip: Pastikan GPS Anda aktif dan berada di sekitar kantor.';
                        }
                        alert('❌ ' + errorMsg);
                        btn.disabled = false;
                        btn.innerHTML = '<span class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Check Out</span>';
                    }
                })
                .catch(error => {
                    alert('❌ Gagal check out: ' + error);
                    btn.disabled = false;
                    btn.innerHTML = '<span class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Check Out</span>';
                });
        }

        // Load status on page load
        document.addEventListener('DOMContentLoaded', function() {
            const banner = document.getElementById('location-banner');
            banner.classList.remove('hidden');
            
            if (!isGeolocationAvailable()) {
                document.getElementById('location-status-text').textContent = '⚠️ Browser tidak mendukung geolokasi';
                document.getElementById('location-detail-text').textContent = 'Gunakan browser modern seperti Chrome atau Firefox';
                document.getElementById('location-icon').className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center';
                document.getElementById('checkin-btn').disabled = true;
                document.getElementById('checkout-btn').disabled = true;
                return;
            }

            requestLocationPermission();
            checkTodayStatus();
            setInterval(checkTodayStatus, 60000);
        });
    </script>
    @endpush
</x-app-layout>
