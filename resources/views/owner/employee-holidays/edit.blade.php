<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Hari Libur Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Edit Hari Libur</p>
                            <h3 class="text-white font-semibold text-lg">
                                {{ $employeeHoliday->employee->user->name ?? 'Karyawan' }}
                            </h3>
                            <p class="text-purple-100 text-sm">
                                Kode: {{ $employeeHoliday->employee->employee_code ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($employeeHoliday->status == 'scheduled') bg-yellow-500/20 text-white
                                @elseif($employeeHoliday->status == 'taken') bg-emerald-500/20 text-white
                                @else bg-red-500/20 text-white @endif">
                                @if($employeeHoliday->status == 'scheduled')
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5"></span>
                                @elseif($employeeHoliday->status == 'taken')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                                @endif
                                {{ $employeeHoliday->getStatusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6">
                    <form method="POST" action="{{ route('owner.employee-holidays.update', $employeeHoliday) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Pilih Karyawan -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Karyawan <span class="text-red-500">*</span>
                            </label>
                            <select id="employee_id" name="employee_id" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('employee_id') border-red-500 @enderror">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                        {{ old('employee_id', $employeeHoliday->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Libur <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="date" name="date" 
                                value="{{ old('date', $employeeHoliday->date->format('Y-m-d')) }}"
                                required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('date') border-red-500 @enderror">
                            @error('date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Libur -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Jenis Libur <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('type') border-red-500 @enderror">
                                <option value="annual" {{ old('type', $employeeHoliday->type) == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                                <option value="sick" {{ old('type', $employeeHoliday->type) == 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                                <option value="personal" {{ old('type', $employeeHoliday->type) == 'personal' ? 'selected' : '' }}>Cuti Pribadi</option>
                                <option value="company" {{ old('type', $employeeHoliday->type) == 'company' ? 'selected' : '' }}>Libur Perusahaan</option>
                                <option value="other" {{ old('type', $employeeHoliday->type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alasan -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                                Alasan / Keterangan
                            </label>
                            <input type="text" id="reason" name="reason" 
                                value="{{ old('reason', $employeeHoliday->reason) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('reason') border-red-500 @enderror"
                                placeholder="Contoh: Libur untuk keperluan keluarga">
                            @error('reason')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('status') border-red-500 @enderror">
                                <option value="scheduled" {{ old('status', $employeeHoliday->status) == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                <option value="taken" {{ old('status', $employeeHoliday->status) == 'taken' ? 'selected' : '' }}>Diambil</option>
                                <option value="cancelled" {{ old('status', $employeeHoliday->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 @error('notes') border-red-500 @enderror"
                                placeholder="Tambahkan catatan jika diperlukan">{{ old('notes', $employeeHoliday->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Informasi Libur</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Dibuat:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeHoliday->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Terakhir Update:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeHoliday->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Hari:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeHoliday->date->format('l, d F Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Jenis:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeHoliday->getTypeLabel() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning jika status sudah diambil -->
                        @if($employeeHoliday->status == 'taken')
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Perhatian!</p>
                                        <p class="text-sm text-yellow-700">Libur ini sudah diambil oleh karyawan. Ubah dengan hati-hati.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('owner.employee-holidays.index') }}"
                                class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-medium rounded-xl hover:from-purple-700 hover:to-pink-600 transition duration-200 shadow-md hover:shadow-lg">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Update Hari Libur
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
