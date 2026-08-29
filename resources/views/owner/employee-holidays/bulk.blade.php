<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Libur Massal</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('owner.employee-holidays.bulk.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Karyawan *</label>
                            <select name="employee_ids[]" multiple required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500" style="min-height: 150px;">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->user->name }} ({{ $employee->employee_code }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Tekan Ctrl/Cmd untuk pilih banyak</p>
                            @error('employee_ids') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Libur *</label>
                            <input type="date" name="date" value="{{ old('date') }}" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                            @error('date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Libur *</label>
                            <select name="type" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-purple-500">
                                <option value="annual">Cuti Tahunan</option>
                                <option value="sick">Cuti Sakit</option>
                                <option value="personal">Cuti Pribadi</option>
                                <option value="company">Libur Perusahaan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Keterangan</label>
                            <input type="text" name="reason" value="{{ old('reason') }}" class="w-full px-4 py-2 border rounded-xl">
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
