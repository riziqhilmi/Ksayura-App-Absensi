<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Penugasan Shift
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-blue-500 to-emerald-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Edit Penugasan Shift</p>
                            <h3 class="text-white font-semibold text-lg">
                                {{ $employeeShift->employee->user->name ?? 'Karyawan' }}
                            </h3>
                            <p class="text-blue-100 text-sm">
                                Kode: {{ $employeeShift->employee->employee_code ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($employeeShift->status == 'active') bg-emerald-500/20 text-white
                                @else bg-red-500/20 text-white @endif">
                                @if($employeeShift->status == 'active')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                                @endif
                                {{ $employeeShift->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6">
                    <form method="POST" action="{{ route('owner.employee-shifts.update', $employeeShift) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Pilih Karyawan -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Karyawan <span class="text-red-500">*</span>
                            </label>
                            <select id="employee_id" name="employee_id" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('employee_id') border-red-500 @enderror">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                        {{ old('employee_id', $employeeShift->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pilih Shift -->
                        <div>
                            <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Shift <span class="text-red-500">*</span>
                            </label>
                            <select id="shift_id" name="shift_id" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('shift_id') border-red-500 @enderror">
                                <option value="">Pilih Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" 
                                        {{ old('shift_id', $employeeShift->shift_id) == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }} 
                                        ({{ date('H:i', strtotime($shift->start_time)) }} - {{ date('H:i', strtotime($shift->end_time)) }})
                                        @if($shift->status == 'inactive')
                                            <span class="text-red-500 text-xs">(Nonaktif)</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('shift_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hari -->
                        <div>
                            <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-1">
                                Hari (Opsional)
                            </label>
                            <select id="day_of_week" name="day_of_week"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('day_of_week') border-red-500 @enderror">
                                <option value="">Setiap Hari</option>
                                <option value="monday" {{ old('day_of_week', $employeeShift->day_of_week) == 'monday' ? 'selected' : '' }}>Senin</option>
                                <option value="tuesday" {{ old('day_of_week', $employeeShift->day_of_week) == 'tuesday' ? 'selected' : '' }}>Selasa</option>
                                <option value="wednesday" {{ old('day_of_week', $employeeShift->day_of_week) == 'wednesday' ? 'selected' : '' }}>Rabu</option>
                                <option value="thursday" {{ old('day_of_week', $employeeShift->day_of_week) == 'thursday' ? 'selected' : '' }}>Kamis</option>
                                <option value="friday" {{ old('day_of_week', $employeeShift->day_of_week) == 'friday' ? 'selected' : '' }}>Jumat</option>
                                <option value="saturday" {{ old('day_of_week', $employeeShift->day_of_week) == 'saturday' ? 'selected' : '' }}>Sabtu</option>
                                <option value="sunday" {{ old('day_of_week', $employeeShift->day_of_week) == 'sunday' ? 'selected' : '' }}>Minggu</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih hari spesifik jika shift hanya berlaku untuk hari tertentu</p>
                            @error('day_of_week')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Periode -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Mulai
                                </label>
                                <input type="date" id="start_date" name="start_date" 
                                    value="{{ old('start_date', $employeeShift->start_date ? date('Y-m-d', strtotime($employeeShift->start_date)) : '') }}"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('start_date') border-red-500 @enderror">
                                @error('start_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Selesai
                                </label>
                                <input type="date" id="end_date" name="end_date" 
                                    value="{{ old('end_date', $employeeShift->end_date ? date('Y-m-d', strtotime($employeeShift->end_date)) : '') }}"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 -mt-2">Kosongkan jika tidak ada batas waktu</p>

                        <!-- Berulang -->
                        <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                            <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                {{ old('is_recurring', $employeeShift->is_recurring) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_recurring" class="text-sm font-medium text-gray-700">
                                Shift ini berulang setiap minggu
                            </label>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status', $employeeShift->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $employeeShift->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('notes') border-red-500 @enderror"
                                placeholder="Tambahkan catatan jika diperlukan">{{ old('notes', $employeeShift->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Informasi Penugasan</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Dibuat:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeShift->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Terakhir Update:</span>
                                    <span class="text-gray-700 ml-1">{{ $employeeShift->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('owner.employee-shifts.index') }}"
                                class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Update Shift
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>