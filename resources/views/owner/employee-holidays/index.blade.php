<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Hari Libur Karyawan
            </h2>
            <a href="{{ route('owner.employee-holidays.calendar') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Tampilan Kalender
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Total Libur</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Terjadwal</p>
                    <p class="mt-2 text-2xl font-bold text-yellow-600">{{ $stats['scheduled'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Diambil</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['taken'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Akan Datang</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['upcoming'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <form method="GET" action="{{ route('owner.employee-holidays.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1">
                        <div>
                            <label for="employee-filter" class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                            <select id="employee-filter" name="employee" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Semua Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="month-filter" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select id="month-filter" name="month" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="year-filter" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <select id="year-filter" name="year" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status-filter" name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Semua Status</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                <option value="taken" {{ request('status') == 'taken' ? 'selected' : '' }}>Diambil</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-xl hover:bg-purple-700 transition">
                                Terapkan Filter
                            </button>
                            <a href="{{ route('owner.employee-holidays.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition">
                                Reset
                            </a>
                        </div>
                    </form>

                    <div class="flex flex-col sm:flex-row gap-2 lg:justify-end">
                        <a href="{{ route('owner.employee-holidays.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm font-medium rounded-xl hover:from-purple-700 hover:to-pink-600 transition shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Libur
                        </a>
                        <a href="{{ route('owner.employee-holidays.bulk') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition shadow-md">
                            Bulk Tambah
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibayar</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                                                {{ strtoupper(substr($holiday->employee->user->name ?? '-', 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-800">{{ $holiday->employee->user->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $holiday->employee->employee_code ?? 'Tanpa kode' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm font-medium text-gray-800">{{ $holiday->date->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $holiday->date->translatedFormat('l') }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                            @if($holiday->type == 'annual') bg-yellow-100 text-yellow-800
                                            @elseif($holiday->type == 'sick') bg-red-100 text-red-800
                                            @elseif($holiday->type == 'personal') bg-blue-100 text-blue-800
                                            @elseif($holiday->type == 'company') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $holiday->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                            @if($holiday->status == 'scheduled') bg-yellow-100 text-yellow-800
                                            @elseif($holiday->status == 'taken') bg-emerald-100 text-emerald-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $holiday->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $holiday->is_paid ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $holiday->is_paid ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('owner.employee-holidays.edit', $holiday) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">
                                                Edit
                                            </a>
                                            <form action="{{ route('owner.employee-holidays.destroy', $holiday) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800" onclick="return confirm('Yakin hapus hari libur ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-14 text-center">
                                        <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700">Belum ada hari libur</p>
                                        <p class="text-sm text-gray-500 mt-1">Coba ubah filter atau tambahkan jadwal libur baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $holidays->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
