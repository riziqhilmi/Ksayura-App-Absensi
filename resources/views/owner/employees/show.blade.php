<x-layouts.owner>
    <x-slot name="header">
        Detail Karyawan
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-blue-500 to-emerald-500 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                            <span class="text-4xl font-bold text-white">{{ substr($employee->user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ $employee->user->name }}</h2>
                            <p class="text-blue-50">{{ $employee->position ?? 'Staff' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium
                        @if($employee->status == 'active') bg-emerald-500/20 text-white
                        @elseif($employee->status == 'inactive') bg-yellow-500/20 text-white
                        @else bg-red-500/20 text-white @endif">
                        @if($employee->status == 'active')
                            <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                        @elseif($employee->status == 'inactive')
                            <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>
                        @endif
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informasi Pribadi
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Nama Lengkap</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->user->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Email</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->user->email }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Telepon</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->user->phone ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Alamat</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->user->address ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-sm text-gray-500">Tanggal Bergabung</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->user->hire_date ? date('d F Y', strtotime($employee->user->hire_date)) : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Informasi Pekerjaan
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Kode Karyawan</span>
                                <span class="text-sm font-mono font-medium text-gray-800">{{ $employee->employee_code }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Posisi</span>
                                <span class="text-sm font-medium text-gray-800">{{ $employee->position ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Tipe Gaji</span>
                                <span class="text-sm font-medium text-gray-800">{{ ucfirst($employee->salary_type) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Gaji Pokok</span>
                                <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</span>
                            </div>
                            @if($employee->daily_rate)
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Tarif Harian</span>
                                <span class="text-sm font-medium text-gray-800">Rp {{ number_format($employee->daily_rate, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @if($employee->hourly_rate)
                            <div class="flex justify-between py-2">
                                <span class="text-sm text-gray-500">Tarif Per Jam</span>
                                <span class="text-sm font-medium text-gray-800">Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end space-x-4">
                    <a href="{{ route('owner.employees.index') }}"
                        class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                        Kembali
                    </a>
                    <a href="{{ route('owner.employees.edit', $employee) }}"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                        Edit Karyawan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.owner>
