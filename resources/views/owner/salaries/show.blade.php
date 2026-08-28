<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Detail Gaji
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-blue-600 to-emerald-500 rounded-2xl shadow-lg p-6 mb-6 text-white">
                <div class="flex flex-wrap items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Periode Gaji</p>
                        <h3 class="text-2xl font-bold">{{ $salary->period }}</h3>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ date('d F Y', strtotime($salary->start_date)) }} - {{ date('d F Y', strtotime($salary->end_date)) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-blue-100 text-sm">Total Gaji</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-1
                            @if($salary->status == 'draft') bg-gray-500/30 text-white
                            @elseif($salary->status == 'calculated') bg-yellow-500/30 text-white
                            @else bg-emerald-500/30 text-white @endif">
                            @if($salary->status == 'draft')
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                            @elseif($salary->status == 'calculated')
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                            @endif
                            {{ ucfirst($salary->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Info Karyawan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($salary->employee->user->name ?? '', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800">{{ $salary->employee->user->name ?? '-' }}</h4>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-1">
                            <span>📋 {{ $salary->employee->employee_code ?? '-' }}</span>
                            <span>💼 {{ $salary->employee->position ?? 'Staff' }}</span>
                            <span>📧 {{ $salary->employee->user->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="ml-auto text-right">
                        <p class="text-sm text-gray-500">Status Karyawan</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($salary->employee->status == 'active') bg-emerald-100 text-emerald-800
                            @elseif($salary->employee->status == 'inactive') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($salary->employee->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Gaji Pokok</p>
                    <p class="text-lg font-bold text-gray-800">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Lembur</p>
                    <p class="text-lg font-bold text-blue-600">Rp {{ number_format($salary->overtime_pay, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">{{ $salary->overtime_hours }} jam</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Bonus & Tunjangan</p>
                    <p class="text-lg font-bold text-emerald-600">Rp {{ number_format($salary->attendance_bonus + $salary->performance_bonus, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500">Potongan</p>
                    <p class="text-lg font-bold text-red-600">Rp {{ number_format($salary->deductions, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Detail Tables -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Komponen Gaji -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h4 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Komponen Gaji
                        </h4>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Gaji Pokok</span>
                            <span class="text-sm font-semibold text-gray-800">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Lembur</span>
                            <span class="text-sm font-semibold text-blue-600">Rp {{ number_format($salary->overtime_pay, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Bonus Kehadiran</span>
                            <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($salary->attendance_bonus, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Bonus Kinerja</span>
                            <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($salary->performance_bonus, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3 border-t-2 border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Subtotal</span>
                            <span class="text-sm font-bold text-gray-800">Rp {{ number_format($salary->base_salary + $salary->overtime_pay + $salary->attendance_bonus + $salary->performance_bonus, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3 bg-red-50">
                            <span class="text-sm font-medium text-red-600">Potongan</span>
                            <span class="text-sm font-bold text-red-600">- Rp {{ number_format($salary->deductions, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-4 bg-emerald-50">
                            <span class="text-sm font-bold text-emerald-700">Total Gaji</span>
                            <span class="text-lg font-bold text-emerald-700">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Statistik Kehadiran -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h4 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Statistik Kehadiran
                        </h4>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Hari Kerja</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $salary->working_days }} hari</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Hadir</span>
                            <span class="text-sm font-semibold text-emerald-600">{{ $salary->present_days }} hari</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Terlambat</span>
                            <span class="text-sm font-semibold text-yellow-600">{{ $salary->late_days }} hari</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Tidak Hadir</span>
                            <span class="text-sm font-semibold text-red-600">{{ $salary->absent_days }} hari</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-sm text-gray-600">Cuti</span>
                            <span class="text-sm font-semibold text-purple-600">{{ $salary->leave_days }} hari</span>
                        </div>
                        <div class="flex justify-between px-6 py-4 bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">Tingkat Kehadiran</span>
                            <span class="text-sm font-bold 
                                @if($salary->working_days > 0 && ($salary->present_days / $salary->working_days) * 100 >= 90) text-emerald-600
                                @elseif($salary->working_days > 0 && ($salary->present_days / $salary->working_days) * 100 >= 75) text-yellow-600
                                @else text-red-600 @endif">
                                {{ $salary->working_days > 0 ? round(($salary->present_days / $salary->working_days) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Kehadiran -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold text-gray-800 text-sm">Tingkat Kehadiran</h4>
                    <span class="text-sm font-bold text-gray-700">
                        {{ $salary->working_days > 0 ? round(($salary->present_days / $salary->working_days) * 100) : 0 }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    @php
                        $percentage = $salary->working_days > 0 ? round(($salary->present_days / $salary->working_days) * 100) : 0;
                        $color = $percentage >= 90 ? 'bg-emerald-500' : ($percentage >= 75 ? 'bg-yellow-500' : 'bg-red-500');
                    @endphp
                    <div class="{{ $color }} h-3 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-gray-400">
                    <span>0%</span>
                    <span>50%</span>
                    <span>100%</span>
                </div>
            </div>

            <!-- Catatan & Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                @if($salary->notes)
                    <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Catatan</p>
                        <p class="text-sm text-gray-800">{{ $salary->notes }}</p>
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    @if($salary->status == 'calculated')
                        <button onclick="markAsPaid({{ $salary->id }})" 
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-blue-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tandai Dibayar
                            </span>
                        </button>
                    @endif

                    @if($salary->status != 'paid')
                        <a href="{{ route('owner.salaries.edit', $salary) }}" 
                           class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Gaji
                            </span>
                        </a>
                    @endif

                    <a href="{{ route('owner.salaries.index') }}" 
                       class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                        Kembali
                    </a>
                </div>

                @if($salary->status == 'paid')
                    <div class="mt-4 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-700">
                                Gaji ini sudah dibayar pada {{ date('d F Y H:i', strtotime($salary->paid_date)) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function markAsPaid(id) {
            if (!confirm('Apakah Anda yakin ingin menandai gaji ini sebagai sudah dibayar?')) return;
            
            const btn = event.target.closest('button');
            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...</span>';
            
            fetch(`/owner/salaries/${id}/paid`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + (data.error || 'Gagal menandai gaji'));
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<span class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Tandai Dibayar</span>';
            });
        }
    </script>
    @endpush
</x-app-layout>