<x-layouts.owner>
    <x-slot name="header">
        Manajemen Gaji
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Gaji</p>
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Draft</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['draft'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Siap Dibayar</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $stats['calculated'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sudah Dibayar</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['paid'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4 mb-6">
            <form action="{{ route('owner.salaries.calculate') }}" method="GET" class="flex items-center gap-2">
                <input type="month" name="period" value="{{ request('period', date('Y-m')) }}" 
                    class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-emerald-500 to-blue-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-blue-700 transition duration-200 shadow-md">
                    Hitung Gaji
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Total Gaji</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($salaries as $salary)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $salary->period }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-sm font-bold">
                                            {{ substr($salary->employee->user->name ?? '', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-800">{{ $salary->employee->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-emerald-600">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($salary->status == 'draft') bg-gray-100 text-gray-800
                                        @elseif($salary->status == 'calculated') bg-emerald-100 text-emerald-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($salary->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('owner.salaries.show', $salary) }}" class="text-blue-600 hover:text-blue-800">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-lg font-medium">Belum ada data gaji</p>
                                    <p class="text-sm">Klik "Hitung Gaji" untuk memulai</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $salaries->links() }}
            </div>
        </div>
    </div>
</x-layouts.owner>