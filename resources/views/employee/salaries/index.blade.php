<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gaji Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl p-5">
                    <p class="text-sm text-gray-500">Total Slip</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl p-5">
                    <p class="text-sm text-gray-500">Total Dibayar</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">Rp {{ number_format($stats['paid'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl p-5">
                    <p class="text-sm text-gray-500">Rata-rata</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">Rp {{ number_format($stats['average'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Riwayat Gaji</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($salaries as $salary)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $salary->period }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $salary->start_date->format('d/m/Y') }} - {{ $salary->end_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                            @if($salary->status === 'paid') bg-emerald-100 text-emerald-700
                                            @elseif($salary->status === 'calculated') bg-blue-100 text-blue-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ ucfirst($salary->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('employee.salaries.show', $salary) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada data gaji.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $salaries->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
