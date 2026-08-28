<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Gaji Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Periode</p>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $salary->period }}</h3>
                    </div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                        @if($salary->status === 'paid') bg-emerald-100 text-emerald-700
                        @elseif($salary->status === 'calculated') bg-blue-100 text-blue-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($salary->status) }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Gaji Pokok</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Lembur</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($salary->overtime_pay, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Bonus Kehadiran</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($salary->attendance_bonus, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Bonus Performa</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($salary->performance_bonus, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Potongan</span>
                        <span class="font-medium text-red-600">Rp {{ number_format($salary->deductions, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="font-semibold text-gray-800">Total Gaji</span>
                        <span class="text-xl font-bold text-emerald-600">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    <a href="{{ route('employee.salaries.my') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
