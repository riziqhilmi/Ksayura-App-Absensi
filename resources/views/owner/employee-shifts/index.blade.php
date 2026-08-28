<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Penugasan Shift Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Total Penugasan</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Aktif</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Tidak Aktif</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>

            <!-- Button Tambah -->
            <div class="flex justify-end mb-6">
                <a href="{{ route('owner.employee-shifts.create') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Assign Shift
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($employeeShifts as $shift)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold">
                                            {{ substr($shift->employee->user->name ?? '', 0, 1) }}
                                        </div>
                                        <span>{{ $shift->employee->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium">{{ $shift->shift->name ?? '-' }}</span>
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        {{ $shift->shift ? date('H:i', strtotime($shift->shift->start_time)) . ' - ' . date('H:i', strtotime($shift->shift->end_time)) : '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $shift->day_of_week ? $shift->getDayOfWeekLabel() : 'Setiap Hari' }}
                                    @if($shift->is_recurring)
                                        <span class="text-xs text-blue-500 ml-1">(Berulang)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($shift->start_date && $shift->end_date)
                                        {{ date('d/m/Y', strtotime($shift->start_date)) }} - {{ date('d/m/Y', strtotime($shift->end_date)) }}
                                    @elseif($shift->start_date)
                                        {{ date('d/m/Y', strtotime($shift->start_date)) }} - seterusnya
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        @if($shift->status == 'active') bg-emerald-100 text-emerald-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $shift->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('owner.employee-shifts.edit', $shift) }}" class="text-emerald-600 hover:text-emerald-800 mr-2">Edit</a>
                                    <form action="{{ route('owner.employee-shifts.destroy', $shift) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada penugasan shift
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $employeeShifts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>