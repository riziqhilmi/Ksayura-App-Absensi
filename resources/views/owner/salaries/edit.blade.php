<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Gaji
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="mb-6">
                    <p class="text-sm text-gray-500">Karyawan</p>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $salary->employee->user->name ?? '-' }}</h3>
                    <p class="text-sm text-gray-500">Periode {{ $salary->period }}</p>
                </div>

                <form method="POST" action="{{ route('owner.salaries.update', $salary) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="base_salary" class="block text-sm font-medium text-gray-700 mb-1">Total Gaji Harian</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="base_salary" name="base_salary" value="{{ old('base_salary', $salary->base_salary) }}" required min="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ number_format($salary->paid_days ?? 0, 1, ',', '.') }} hari x Rp {{ number_format($salary->daily_rate ?? 0, 0, ',', '.') }}</p>
                            @error('base_salary')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="overtime_pay" class="block text-sm font-medium text-gray-700 mb-1">Lembur</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="overtime_pay" name="overtime_pay" value="{{ old('overtime_pay', $salary->overtime_pay) }}" required min="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label for="attendance_bonus" class="block text-sm font-medium text-gray-700 mb-1">Bonus Kehadiran</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="attendance_bonus" name="attendance_bonus" value="{{ old('attendance_bonus', $salary->attendance_bonus) }}" required min="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label for="performance_bonus" class="block text-sm font-medium text-gray-700 mb-1">Bonus Kinerja</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="performance_bonus" name="performance_bonus" value="{{ old('performance_bonus', $salary->performance_bonus) }}" required min="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label for="deductions" class="block text-sm font-medium text-gray-700 mb-1">Potongan</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="deductions" name="deductions" value="{{ old('deductions', $salary->deductions) }}" required min="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $salary->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('owner.salaries.show', $salary) }}"
                            class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition shadow-md">
                            Simpan Gaji
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
