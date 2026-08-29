<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Hari Libur Karyawan</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('owner.employee-holidays.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan *</label>
                            <select name="employee_id" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                            <input type="date" name="date" value="{{ old('date') }}" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                            @error('date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Libur *</label>
                            <select name="type" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                                <option value="annual" {{ old('type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                                <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                                <option value="personal" {{ old('type') == 'personal' ? 'selected' : '' }}>Cuti Pribadi</option>
                                <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>Libur Perusahaan</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Keterangan</label>
                            <input type="text" name="reason" value="{{ old('reason') }}" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                        <a href="{{ route('owner.employee-holidays.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
