<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EmployeeShiftController extends Controller
{
    // Tampilkan semua penugasan shift
    public function index(Request $request)
    {
        $query = EmployeeShift::with(['employee.user', 'shift']);
        
        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employeeShifts = $query->latest()->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();

        $stats = [
            'total' => $employeeShifts->count(),
            'active' => $employeeShifts->where('status', 'active')->count(),
            'inactive' => $employeeShifts->where('status', 'inactive')->count(),
        ];

        return Inertia::render('Owner/EmployeeShifts/Index', [
            'employeeShifts' => $employeeShifts->through(fn (EmployeeShift $employeeShift) => $this->employeeShiftPayload($employeeShift)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOption($employee))->values(),
            'rollingEmployees' => EmployeeShift::with(['employee.user', 'shift'])
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('notes')
                        ->orWhere('notes', 'not like', '[Rolling Shift]%');
                })
                ->orderBy('employee_id')
                ->get()
                ->map(fn (EmployeeShift $employeeShift) => $this->rollingEmployeeOption($employeeShift))
                ->values(),
            'shifts' => $shifts->map(fn (Shift $shift) => $this->shiftOption($shift))->values(),
            'stats' => $stats,
            'filters' => [
                'employee' => $request->employee,
                'status' => $request->status,
            ],
            'links' => [
                'create' => route('owner.employee-shifts.create'),
                'index' => route('owner.employee-shifts.index'),
                'rollingShift' => route('owner.employee-shifts.rolling-shift'),
            ],
        ]);
    }

    // Form tambah shift karyawan
    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        return Inertia::render('Owner/EmployeeShifts/Form', [
            'mode' => 'create',
            'employeeShift' => null,
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOptionWithAssignments($employee))->values(),
            'shifts' => $shifts->map(fn (Shift $shift) => $this->shiftOption($shift))->values(),
            'links' => [
                'index' => route('owner.employee-shifts.index'),
                'submit' => route('owner.employee-shifts.store'),
            ],
        ]);
    }

    // Simpan shift karyawan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $conflictingShift = $this->conflictingShift($request);

        if ($conflictingShift) {
            return redirect()->back()
                ->with('error', $this->conflictMessage($conflictingShift))
                ->withInput();
        }

        EmployeeShift::create([
            'employee_id' => $request->employee_id,
            'shift_id' => $request->shift_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'day_of_week' => $request->day_of_week,
            'is_recurring' => $request->is_recurring ?? false,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.employee-shifts.index')
            ->with('success', 'Shift karyawan berhasil ditambahkan!');
    }

    // Edit shift karyawan
    public function edit(EmployeeShift $employeeShift)
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        $employeeShift->load(['employee.user', 'shift']);

        return Inertia::render('Owner/EmployeeShifts/Form', [
            'mode' => 'edit',
            'employeeShift' => $this->employeeShiftPayload($employeeShift),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOptionWithAssignments($employee))->values(),
            'shifts' => $shifts->map(fn (Shift $shift) => $this->shiftOption($shift))->values(),
            'links' => [
                'index' => route('owner.employee-shifts.index'),
                'submit' => route('owner.employee-shifts.update', $employeeShift),
            ],
        ]);
    }

    // Update shift karyawan
    public function update(Request $request, EmployeeShift $employeeShift)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'is_recurring' => 'boolean',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->status === 'active') {
            $conflictingShift = $this->conflictingShift($request, $employeeShift);

            if ($conflictingShift) {
                return redirect()->back()
                    ->with('error', $this->conflictMessage($conflictingShift))
                    ->withInput();
            }
        }

        $employeeShift->update([
            'employee_id' => $request->employee_id,
            'shift_id' => $request->shift_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'day_of_week' => $request->day_of_week,
            'is_recurring' => $request->is_recurring ?? false,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.employee-shifts.index')
            ->with('success', 'Shift karyawan berhasil diperbarui!');
    }

    // Hapus shift karyawan
    public function destroy(EmployeeShift $employeeShift)
    {
        $employeeShift->delete();
        return redirect()->route('owner.employee-shifts.index')
            ->with('success', 'Shift karyawan berhasil dihapus!');
    }

    public function rollingShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_employee_shift_id' => ['required', 'exists:employee_shifts,id', 'different:to_employee_shift_id'],
            'to_employee_shift_id' => ['required', 'exists:employee_shifts,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ], [
            'from_employee_shift_id.different' => 'Pilih dua penugasan shift yang berbeda untuk rolling shift.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fromAssignment = EmployeeShift::with(['employee.user', 'shift'])
            ->whereKey($request->from_employee_shift_id)
            ->where('status', 'active')
            ->first();

        $toAssignment = EmployeeShift::with(['employee.user', 'shift'])
            ->whereKey($request->to_employee_shift_id)
            ->where('status', 'active')
            ->first();

        if (!$fromAssignment || !$toAssignment) {
            return redirect()->back()
                ->with('error', 'Rolling shift hanya bisa dilakukan jika kedua karyawan punya penugasan shift aktif.');
        }

        if ($fromAssignment->employee_id === $toAssignment->employee_id) {
            return redirect()->back()
                ->with('error', 'Rolling shift harus dilakukan antara dua karyawan berbeda.');
        }

        if (
            $this->hasTemporaryRollingConflict($fromAssignment->employee_id, $request->start_date, $request->end_date)
            || $this->hasTemporaryRollingConflict($toAssignment->employee_id, $request->start_date, $request->end_date)
        ) {
            return redirect()->back()
                ->with('error', 'Rolling shift tidak bisa dibuat karena salah satu karyawan sudah punya rolling sementara pada rentang tanggal tersebut.');
        }

        DB::transaction(function () use ($request, $fromAssignment, $toAssignment) {
            $period = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');

            EmployeeShift::create([
                'employee_id' => $fromAssignment->employee_id,
                'shift_id' => $toAssignment->shift_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'day_of_week' => null,
                'is_recurring' => false,
                'status' => 'active',
                'notes' => '[Rolling Shift] Tukar sementara dengan ' . ($toAssignment->employee?->user?->name ?? 'karyawan lain') . ' pada ' . $period,
            ]);

            EmployeeShift::create([
                'employee_id' => $toAssignment->employee_id,
                'shift_id' => $fromAssignment->shift_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'day_of_week' => null,
                'is_recurring' => false,
                'status' => 'active',
                'notes' => '[Rolling Shift] Tukar sementara dengan ' . ($fromAssignment->employee?->user?->name ?? 'karyawan lain') . ' pada ' . $period,
            ]);
        });

        return redirect()->route('owner.employee-shifts.index')
            ->with('success', 'Rolling shift sementara berhasil dibuat. Setelah tanggal selesai, karyawan otomatis kembali ke shift awal.');
    }

    private function employeeShiftPayload(EmployeeShift $employeeShift): array
    {
        return [
            'id' => $employeeShift->id,
            'employee_id' => $employeeShift->employee_id,
            'shift_id' => $employeeShift->shift_id,
            'employee_name' => $employeeShift->employee?->user?->name ?? '-',
            'employee_code' => $employeeShift->employee?->employee_code ?? '-',
            'shift_name' => $employeeShift->shift?->name ?? '-',
            'shift_time' => $employeeShift->shift ? $this->formatTime($employeeShift->shift->start_time) . ' - ' . $this->formatTime($employeeShift->shift->end_time) : '',
            'day_of_week' => $employeeShift->day_of_week,
            'day_label' => $employeeShift->day_of_week ? $employeeShift->getDayOfWeekLabel() : 'Setiap Hari',
            'start_date' => $employeeShift->start_date ? Carbon::parse($employeeShift->start_date)->format('Y-m-d') : null,
            'end_date' => $employeeShift->end_date ? Carbon::parse($employeeShift->end_date)->format('Y-m-d') : null,
            'period_label' => $this->periodLabel($employeeShift),
            'is_recurring' => (bool) $employeeShift->is_recurring,
            'status' => $employeeShift->status,
            'notes' => $employeeShift->notes,
            'created_at' => optional($employeeShift->created_at)->format('d/m/Y H:i'),
            'updated_at' => optional($employeeShift->updated_at)->format('d/m/Y H:i'),
            'urls' => [
                'edit' => route('owner.employee-shifts.edit', $employeeShift),
                'destroy' => route('owner.employee-shifts.destroy', $employeeShift),
            ],
        ];
    }

    private function employeeOption(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $employee->user?->name,
            'employee_code' => $employee->employee_code,
            'label' => ($employee->user?->name ?? 'Karyawan') . ' (' . $employee->employee_code . ')',
        ];
    }

    private function employeeOptionWithAssignments(Employee $employee): array
    {
        return array_merge($this->employeeOption($employee), [
            'active_assignments' => EmployeeShift::with('shift')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->orderBy('start_date')
                ->get()
                ->map(fn (EmployeeShift $employeeShift) => [
                    'id' => $employeeShift->id,
                    'shift_name' => $employeeShift->shift?->name ?? '-',
                    'shift_time' => $employeeShift->shift ? $this->formatTime($employeeShift->shift->start_time) . ' - ' . $this->formatTime($employeeShift->shift->end_time) : '',
                    'day_of_week' => $employeeShift->day_of_week,
                    'day_label' => $employeeShift->day_of_week ? $employeeShift->getDayOfWeekLabel() : 'Setiap Hari',
                    'start_date' => $employeeShift->start_date ? Carbon::parse($employeeShift->start_date)->format('Y-m-d') : null,
                    'end_date' => $employeeShift->end_date ? Carbon::parse($employeeShift->end_date)->format('Y-m-d') : null,
                    'period_label' => $this->periodLabel($employeeShift),
                ])
                ->values(),
        ]);
    }

    private function shiftOption(Shift $shift): array
    {
        return [
            'id' => $shift->id,
            'name' => $shift->name,
            'start_time' => $this->formatTime($shift->start_time),
            'end_time' => $this->formatTime($shift->end_time),
            'label' => $shift->name . ' (' . $this->formatTime($shift->start_time) . ' - ' . $this->formatTime($shift->end_time) . ')',
        ];
    }

    private function rollingEmployeeOption(EmployeeShift $employeeShift): array
    {
        return [
            'assignment_id' => $employeeShift->id,
            'employee_id' => $employeeShift->employee_id,
            'employee_name' => $employeeShift->employee?->user?->name ?? '-',
            'employee_code' => $employeeShift->employee?->employee_code ?? '-',
            'shift_name' => $employeeShift->shift?->name ?? '-',
            'shift_time' => $employeeShift->shift ? $this->formatTime($employeeShift->shift->start_time) . ' - ' . $this->formatTime($employeeShift->shift->end_time) : '',
            'day_of_week' => $employeeShift->day_of_week,
            'day_label' => $employeeShift->day_of_week ? $employeeShift->getDayOfWeekLabel() : 'Setiap Hari',
            'start_date' => $employeeShift->start_date ? Carbon::parse($employeeShift->start_date)->format('Y-m-d') : null,
            'end_date' => $employeeShift->end_date ? Carbon::parse($employeeShift->end_date)->format('Y-m-d') : null,
            'period_label' => $this->periodLabel($employeeShift),
            'label' => ($employeeShift->employee?->user?->name ?? 'Karyawan') . ' (' . ($employeeShift->employee?->employee_code ?? '-') . ') - ' . ($employeeShift->shift?->name ?? 'Shift') . ' | ' . ($employeeShift->day_of_week ? $employeeShift->getDayOfWeekLabel() : 'Setiap Hari') . ' | ' . $this->periodLabel($employeeShift),
        ];
    }

    private function conflictingShift(Request $request, ?EmployeeShift $except = null): ?EmployeeShift
    {
        return $this->conflictingShiftForData(
            (int) $request->employee_id,
            $request->start_date,
            $request->end_date,
            $request->day_of_week,
            $except
        );
    }

    private function conflictingShiftForData(
        int $employeeId,
        $startDate,
        $endDate,
        ?string $dayOfWeek,
        ?EmployeeShift $except = null
    ): ?EmployeeShift {
        $startDate = $startDate ? Carbon::parse($startDate)->toDateString() : '1000-01-01';
        $endDate = $endDate ? Carbon::parse($endDate)->toDateString() : '9999-12-31';

        return EmployeeShift::with(['shift'])
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->where(function ($query) use ($endDate) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $endDate);
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->when($dayOfWeek, function ($query) use ($dayOfWeek) {
                $query->where(function ($dayQuery) use ($dayOfWeek) {
                    $dayQuery->whereNull('day_of_week')
                        ->orWhere('day_of_week', $dayOfWeek);
                });
            })
            ->first();
    }

    private function conflictMessage(EmployeeShift $employeeShift): string
    {
        $day = $employeeShift->day_of_week ? $employeeShift->getDayOfWeekLabel() : 'setiap hari';

        return 'Karyawan ini sudah ada shift pada tanggal tersebut: '
            . ($employeeShift->shift?->name ?? 'Shift')
            . ' (' . $day . ', ' . $this->periodLabel($employeeShift) . ').';
    }

    private function hasTemporaryRollingConflict(int $employeeId, string $startDate, string $endDate): bool
    {
        return EmployeeShift::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('notes', 'like', '[Rolling Shift]%')
            ->where(function ($query) use ($endDate) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $endDate);
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->exists();
    }

    private function formatTime($value): ?string
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    private function periodLabel(EmployeeShift $employeeShift): string
    {
        if ($employeeShift->start_date && $employeeShift->end_date) {
            return Carbon::parse($employeeShift->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($employeeShift->end_date)->format('d/m/Y');
        }

        if ($employeeShift->start_date) {
            return Carbon::parse($employeeShift->start_date)->format('d/m/Y') . ' - seterusnya';
        }

        return '-';
    }
}
