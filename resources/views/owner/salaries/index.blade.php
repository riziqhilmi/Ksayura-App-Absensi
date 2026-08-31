<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center text-xl font-semibold leading-tight text-gray-800">
                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Manajemen Gaji
            </h2>
            <form action="{{ route('owner.salaries.calculate') }}" method="GET" class="flex items-center gap-2">
                <input type="month" name="period" value="{{ request('period', date('Y-m')) }}" 
                    class="rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-6 3v-3m-3 6h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Hitung Gaji
                </button>
            </form>
        </div>
    </x-slot>

    @php
        $salaryStatusColors = [
            'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'calculated' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'paid' => 'bg-blue-50 text-blue-700 ring-blue-200',
        ];
        $salaryStatusLabels = [
            'draft' => 'Draft',
            'calculated' => 'Siap Dibayar',
            'paid' => 'Sudah Dibayar',
        ];
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
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

            <!-- Filter Section -->
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Daftar Gaji</h3>
                        <p class="mt-1 text-sm text-slate-500">Cari, filter, dan kelola data gaji karyawan.</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="searchSalary" placeholder="Cari karyawan..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>

                        <select id="filterStatus" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="calculated">Siap Dibayar</option>
                            <option value="paid">Sudah Dibayar</option>
                        </select>

                        <select id="filterPeriod" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-44">
                            <option value="">Semua Periode</option>
                            @php
                                $months = [];
                                for ($i = 0; $i < 12; $i++) {
                                    $date = \Carbon\Carbon::now()->subMonths($i);
                                    $months[$date->format('Y-m')] = $date->format('F Y');
                                }
                            @endphp
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" {{ request('period') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
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
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Periode</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Karyawan</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 lg:table-cell">Hari Dibayar</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Gaji Harian</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Total Gaji</th>
                                <th class="hidden px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 xl:table-cell">Status</th>
                                <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="salaryTableBody">
                            @forelse($salaries as $salary)
                                @php
                                    $statusClass = $salaryStatusColors[$salary->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                                    $statusLabel = $salaryStatusLabels[$salary->status] ?? ucfirst($salary->status);
                                    $paidDays = $salary->paid_days ?? ($salary->present_days + $salary->late_days);
                                    $dailyRate = $salary->daily_rate ?? $salary->employee->daily_rate ?? 0;
                                @endphp
                                <tr class="salary-row transition hover:bg-slate-50" 
                                    data-name="{{ strtolower($salary->employee->user->name ?? '') }}" 
                                    data-status="{{ $salary->status }}"
                                    data-period="{{ $salary->period }}">
                                    <td class="px-5 py-4">
                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-mono text-xs font-semibold text-blue-700">{{ $salary->period }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-emerald-500 text-sm font-bold text-white">
                                                {{ substr($salary->employee->user->name ?? '', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900">{{ $salary->employee->user->name ?? '-' }}</p>
                                                <p class="truncate text-xs text-slate-500">{{ $salary->employee->employee_code ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden px-5 py-4 lg:table-cell">
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ number_format($paidDays, 1, ',', '.') }} hari</p>
                                            <p class="text-xs text-slate-500">{{ $salary->present_days }} hadir, {{ $salary->late_days }} terlambat</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="whitespace-nowrap text-sm font-bold text-emerald-600">Rp {{ number_format($dailyRate, 0, ',', '.') }}</p>
                                        <p class="text-xs text-slate-400">per hari</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-emerald-700">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</p>
                                        <p class="text-xs text-slate-400">{{ number_format($paidDays, 1, ',', '.') }} × Rp {{ number_format($dailyRate, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="hidden px-5 py-4 xl:table-cell">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('owner.salaries.show', $salary) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            @if($salary->status != 'paid')
                                                <a href="{{ route('owner.salaries.edit', $salary) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                @if($salary->status == 'calculated')
                                                    <button onclick="markAsPaid({{ $salary->id }})" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Tandai Dibayar">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <button onclick="confirmDelete({{ $salary->id }})" class="rounded-xl p-2 text-red-600 transition hover:bg-red-50" title="Hapus">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            <form id="delete-form-{{ $salary->id }}" action="{{ route('owner.salaries.destroy', $salary) }}" method="POST" class="hidden">
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-lg font-bold text-slate-600">Belum ada data gaji</p>
                                        <p class="mt-1 text-sm text-slate-400">Klik "Hitung Gaji" untuk mulai menghitung gaji karyawan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Card Mobile -->
            <section class="space-y-4 md:hidden">
                @forelse($salaries as $salary)
                    @php
                        $statusClass = $salaryStatusColors[$salary->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                        $statusLabel = $salaryStatusLabels[$salary->status] ?? ucfirst($salary->status);
                        $paidDays = $salary->paid_days ?? ($salary->present_days + $salary->late_days);
                        $dailyRate = $salary->daily_rate ?? $salary->employee->daily_rate ?? 0;
                    @endphp
                    <article class="salary-card rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md" 
                             data-name="{{ strtolower($salary->employee->user->name ?? '') }}" 
                             data-status="{{ $salary->status }}"
                             data-period="{{ $salary->period }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-emerald-500 text-base font-bold text-white">
                                    {{ substr($salary->employee->user->name ?? '', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $salary->employee->user->name ?? '-' }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $salary->employee->employee_code ?? '-' }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Periode</p>
                                <p class="mt-1 font-mono font-semibold text-slate-700">{{ $salary->period }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Hari Dibayar</p>
                                <p class="mt-1 font-bold text-slate-700">{{ number_format($paidDays, 1, ',', '.') }} hari</p>
                                <p class="text-xs text-slate-400">{{ $salary->present_days }} hadir, {{ $salary->late_days }} terlambat</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Gaji Harian</p>
                                <p class="mt-1 font-bold text-emerald-600">Rp {{ number_format($dailyRate, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Total Gaji</p>
                                <p class="mt-1 font-bold text-emerald-700">Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ number_format($paidDays, 1, ',', '.') }} × Rp {{ number_format($dailyRate, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-1 border-t border-slate-100 pt-3">
                            <a href="{{ route('owner.salaries.show', $salary) }}" class="rounded-xl p-2 text-blue-600 transition hover:bg-blue-50" title="Detail">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($salary->status != 'paid')
                                <a href="{{ route('owner.salaries.edit', $salary) }}" class="rounded-xl p-2 text-emerald-600 transition hover:bg-emerald-50" title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <svg class="mx-auto mb-4 h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-bold text-slate-600">Belum ada data gaji</p>
                        <p class="mt-1 text-sm text-slate-400">Klik "Hitung Gaji" untuk mulai menghitung gaji karyawan.</p>
                    </div>
                @endforelse
            </section>

            <!-- Pagination -->
            @if($salaries->hasPages())
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    {{ $salaries->links() }}
                </section>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data gaji ini?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }

        function markAsPaid(id) {
            if (!confirm('Apakah Anda yakin ingin menandai gaji ini sebagai sudah dibayar?')) return;
            
            const btn = event.target.closest('button');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            fetch(`/owner/salaries/${id}/paid`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + (data.error || 'Gagal menandai gaji'));
                }
            })
            .catch(error => {
                alert('❌ Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchSalary');
            const filterStatus = document.getElementById('filterStatus');
            const filterPeriod = document.getElementById('filterPeriod');

            function filterSalaries() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusFilter = filterStatus.value;
                const periodFilter = filterPeriod.value;
                const items = document.querySelectorAll('.salary-row, .salary-card');

                items.forEach((item) => {
                    const name = item.dataset.name || '';
                    const status = item.dataset.status || '';
                    const period = item.dataset.period || '';
                    const matchSearch = name.includes(searchTerm);
                    const matchStatus = statusFilter === '' || status === statusFilter;
                    const matchPeriod = periodFilter === '' || period === periodFilter;

                    item.style.display = matchSearch && matchStatus && matchPeriod ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterSalaries);
            filterStatus.addEventListener('change', filterSalaries);
            filterPeriod.addEventListener('change', filterSalaries);
        });
    </script>
    @endpush
</x-app-layout>