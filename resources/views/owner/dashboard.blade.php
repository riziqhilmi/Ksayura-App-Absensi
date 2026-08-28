<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Dashboard Owner
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Karyawan -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Karyawan</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_employees'] ?? 0 }}</p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-emerald-600 font-medium">+{{ $stats['new_employees'] ?? 0 }}</span>
                        <span class="text-gray-500 ml-2">bulan ini</span>
                    </div>
                </div>

                <!-- Karyawan Aktif -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Karyawan Aktif</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['active_employees'] ?? 0 }}</p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-emerald-600 font-medium">{{ $stats['active_percentage'] ?? 0 }}%</span>
                        <span class="text-gray-500 ml-2">dari total</span>
                    </div>
                </div>

                <!-- Total Gaji Bulan Ini -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Gaji Bulan Ini</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">Rp {{ number_format($stats['total_salary'] ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-gray-500">Belum termasuk bonus</span>
                    </div>
                </div>

                <!-- Total Absensi Hari Ini -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Absensi Hari Ini</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['today_attendance'] ?? 0 }}</p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-emerald-600 font-medium">{{ $stats['attendance_percentage'] ?? 0 }}%</span>
                        <span class="text-gray-500 ml-2">kehadiran</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
                <div class="space-y-4">
                    @if(isset($recent_activities) && count($recent_activities) > 0)
                        @foreach($recent_activities as $activity)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-xl">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm font-bold">{{ substr($activity['user'], 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ $activity['user'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['action'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>Belum ada aktivitas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>