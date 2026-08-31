<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Manajemen Shift
            </h2>
            <a href="{{ route('owner.shifts.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Tambah Shift
            </a>
        </div>
    </x-slot>

    @php
        $shiftStatusColors = [
            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $shiftStatusLabels = [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
        ];
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Shift</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $shifts->count() }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Shift Aktif</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeShifts }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Dari total shift</span>
                            <span class="font-bold text-emerald-600">{{ $shifts->count() > 0 ? round(($activeShifts / $shifts->count()) * 100) : 0 }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $shifts->count() > 0 ? round(($activeShifts / $shifts->count()) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Shift Nonaktif</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $inactiveShifts }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter Section -->
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Daftar Shift</h3>
                        <p class="mt-1 text-sm text-slate-500">Cari, filter, dan kelola data shift.</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="searchShift" placeholder="Cari nama shift..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>

                        <select id="filterStatus" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Table Desktop -->
            <section class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Shift</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jam Mulai</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jam Selesai</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 lg:table-cell">Jam Istirahat</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Toleransi</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 xl:table-cell">Status</th>
                                <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="shiftTableBody">
                            @forelse($shifts as $shift)
                                @php
                                    $statusClass = $shiftStatusColors[$shift->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                                    $statusLabel = $shiftStatusLabels[$shift->status] ?? ucfirst($shift->status);
                                @endphp
                                <tr class="shift-row transition hover:bg-slate-50" data-name="{{ strtolower($shift->name) }}" data-status="{{ $shift->status }}">
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-emerald-500 text-sm font-bold text-white">
                                                {{ substr($shift->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900">{{ $shift->name }}</p>
                                                @if($shift->notes)
                                                    <p class="truncate text-xs text-slate-500">{{ Str::limit($shift->notes, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ date('H:i', strtotime($shift->start_time)) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ date('H:i', strtotime($shift->end_time)) }}</span>
                                    </td>
                                    <td class="hidden px-5 py-4 lg:table-cell">
                                        @if($shift->break_start && $shift->break_end)
                                            <span class="text-sm text-slate-600">
                                                {{ date('H:i', strtotime($shift->break_start)) }} - {{ date('H:i', strtotime($shift->break_end)) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $shift->grace_period }} menit</span>
                                    </td>
                                    <td class="hidden px-5 py-4 xl:table-cell">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $shift->status == 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('owner.shifts.show', $shift) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('owner.shifts.edit', $shift) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button onclick="toggleStatus('{{ $shift->id }}', '{{ $shift->status }}')" 
                                                    class="rounded-xl p-2 {{ $shift->status == 'active' ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition" 
                                                    title="{{ $shift->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                @if($shift->status == 'active')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                            <button onclick="confirmDelete(this)" class="rounded-xl p-2 text-red-600 transition hover:bg-red-50" title="Hapus">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('owner.shifts.destroy', $shift) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <svg class="mx-auto mb-4 h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-lg font-bold text-slate-600">Belum ada shift</p>
                                        <p class="mt-1 text-sm text-slate-400">Tambahkan shift pertama untuk mulai mengelola data.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Card Mobile -->
            <section class="space-y-4 md:hidden">
                @forelse($shifts as $shift)
                    @php
                        $statusClass = $shiftStatusColors[$shift->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                        $statusLabel = $shiftStatusLabels[$shift->status] ?? ucfirst($shift->status);
                    @endphp
                    <article class="shift-card rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md" data-name="{{ strtolower($shift->name) }}" data-status="{{ $shift->status }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-emerald-500 text-base font-bold text-white">
                                    {{ substr($shift->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $shift->name }}</p>
                                    @if($shift->notes)
                                        <p class="truncate text-xs text-slate-500">{{ Str::limit($shift->notes, 25) }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Jam Mulai</p>
                                <p class="mt-1 font-mono font-semibold text-slate-700">{{ date('H:i', strtotime($shift->start_time)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Jam Selesai</p>
                                <p class="mt-1 font-mono font-semibold text-slate-700">{{ date('H:i', strtotime($shift->end_time)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Istirahat</p>
                                <p class="mt-1 font-semibold text-slate-700">
                                    @if($shift->break_start && $shift->break_end)
                                        {{ date('H:i', strtotime($shift->break_start)) }} - {{ date('H:i', strtotime($shift->break_end)) }}
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Toleransi</p>
                                <p class="mt-1 font-semibold text-amber-700">{{ $shift->grace_period }} menit</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-1 border-t border-slate-100 pt-3">
                            <a href="{{ route('owner.shifts.show', $shift) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('owner.shifts.edit', $shift) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button onclick="toggleStatus('{{ $shift->id }}', '{{ $shift->status }}')" 
                                    class="rounded-xl p-2 {{ $shift->status == 'active' ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition" 
                                    title="{{ $shift->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if($shift->status == 'active')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                            <button onclick="confirmDelete(this)" class="rounded-xl p-2 text-red-600 transition hover:bg-red-50" title="Hapus">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <form action="{{ route('owner.shifts.destroy', $shift) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <svg class="mx-auto mb-4 h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-bold text-slate-600">Belum ada shift</p>
                        <p class="mt-1 text-sm text-slate-400">Tambahkan shift pertama untuk mulai mengelola data.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(button) {
            if (confirm('Apakah Anda yakin ingin menghapus shift ini?')) {
                button.parentElement.querySelector('form').submit();
            }
        }

        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const message = currentStatus === 'active' 
                ? 'Apakah Anda yakin ingin menonaktifkan shift ini?' 
                : 'Apakah Anda yakin ingin mengaktifkan shift ini?';
            
            if (confirm(message)) {
                fetch(`/owner/shifts/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal mengubah status shift');
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchShift');
            const filterStatus = document.getElementById('filterStatus');

            function filterShifts() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusFilter = filterStatus.value;
                const shifts = document.querySelectorAll('.shift-row, .shift-card');

                shifts.forEach((shift) => {
                    const name = shift.dataset.name || '';
                    const status = shift.dataset.status || '';
                    const matchSearch = name.includes(searchTerm);
                    const matchStatus = statusFilter === '' || status === statusFilter;

                    shift.style.display = matchSearch && matchStatus ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterShifts);
            filterStatus.addEventListener('change', filterShifts);
        });
    </script>
    @endpush
</x-app-layout>