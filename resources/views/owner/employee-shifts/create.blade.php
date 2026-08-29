<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Assign Shift ke Karyawan</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('owner.employee-shifts.store') }}">
                    @csrf

                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan *</label>
                            <select name="employee_id" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    @php
                                        $alreadyAssigned = in_array($employee->id, $assignedEmployeeIds ?? []);
                                    @endphp
                                    <option value="{{ $employee->id }}"
                                        {{ old('employee_id') == $employee->id && !$alreadyAssigned ? 'selected' : '' }}
                                        {{ $alreadyAssigned ? 'disabled' : '' }}>
                                        {{ $employee->user->name }} ({{ $employee->employee_code }}){{ $alreadyAssigned ? ' - sudah punya shift' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Karyawan yang sudah punya penugasan shift aktif tidak bisa dipilih lagi.</p>
                            @error('employee_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shift *</label>
                            <select name="shift_id" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }} ({{ date('H:i', strtotime($shift->start_time)) }} - {{ date('H:i', strtotime($shift->end_time)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('shift_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hari (Opsional)</label>
                            <select name="day_of_week" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Setiap Hari</option>
                                <option value="monday" {{ old('day_of_week') == 'monday' ? 'selected' : '' }}>Senin</option>
                                <option value="tuesday" {{ old('day_of_week') == 'tuesday' ? 'selected' : '' }}>Selasa</option>
                                <option value="wednesday" {{ old('day_of_week') == 'wednesday' ? 'selected' : '' }}>Rabu</option>
                                <option value="thursday" {{ old('day_of_week') == 'thursday' ? 'selected' : '' }}>Kamis</option>
                                <option value="friday" {{ old('day_of_week') == 'friday' ? 'selected' : '' }}>Jumat</option>
                                <option value="saturday" {{ old('day_of_week') == 'saturday' ? 'selected' : '' }}>Sabtu</option>
                                <option value="sunday" {{ old('day_of_week') == 'sunday' ? 'selected' : '' }}>Minggu</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                            <label class="ml-2 text-sm text-gray-700">Berulang (setiap minggu)</label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                        <a href="{{ route('owner.employee-shifts.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-emerald-500 text-white rounded-xl hover:from-blue-700 hover:to-emerald-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
