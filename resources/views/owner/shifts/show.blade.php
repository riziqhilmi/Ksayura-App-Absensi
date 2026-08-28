<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Detail Shift
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-emerald-500 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ substr($shift->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white">{{ $shift->name }}</h2>
                                <p class="text-blue-50">{{ $shift->notes ?? 'Tidak ada catatan' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium
                            @if($shift->status == 'active') bg-emerald-500/20 text-white
                            @else bg-red-500/20 text-white @endif">
                            @if($shift->status == 'active')
                                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>
                            @endif
                            {{ $shift->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Waktu Shift -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Waktu Shift
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Jam Mulai</span>
                                    <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->start_time)) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Jam Selesai</span>
                                    <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->end_time)) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Durasi</span>
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ \Carbon\Carbon::parse($shift->start_time)->diffInHours(\Carbon\Carbon::parse($shift->end_time)) }} jam
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Istirahat & Toleransi -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                                Istirahat & Toleransi
                            </h3>
                            <div class="space-y-3">
                                @if($shift->break_start && $shift->break_end)
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-500">Mulai Istirahat</span>
                                        <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->break_start)) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-500">Selesai Istirahat</span>
                                        <span class="text-sm font-medium text-gray-800">{{ date('H:i', strtotime($shift->break_end)) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-500">Durasi Istirahat</span>
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($shift->break_start)->diffInMinutes(\Carbon\Carbon::parse($shift->break_end)) }} menit
                                        </span>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-400">
                                        <p>Tidak ada waktu istirahat</p>
                                    </div>
                                @endif
                                <div class="flex justify-between py-2">
                                    <span class="text-sm text-gray-500">Toleransi Keterlambatan</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $shift->grace_period }} menit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informasi Lainnya
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Dibuat</span>
                                <span class="text-sm font-medium text-gray-800">{{ $shift->created_at->format('d F Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Terakhir Diupdate</span>
                                <span class="text-sm font-medium text-gray-800">{{ $shift->updated_at->format('d F Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end space-x-4">
                        <a href="{{ route('owner.shifts.index') }}"
                            class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                            Kembali
                        </a>
                        <a href="{{ route('owner.shifts.edit', $shift) }}"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                            Edit Shift
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>