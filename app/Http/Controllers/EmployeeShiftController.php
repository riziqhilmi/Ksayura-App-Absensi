<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            'shifts' => $shifts->map(fn (Shift $shift) => $this->shiftOption($shift))->values(),
            'stats' => $stats,
            'filters' => [
                'employee' => $request->employee,
                'status' => $request->status,
            ],
            'links' => [
                'create' => route('owner.employee-shifts.create'),
                'index' => route('owner.employee-shifts.index'),
            ],
        ]);
    }

    // Form tambah shift karyawan
    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        $assignedEmployeeIds = EmployeeShift::where('status', 'active')
            ->pluck('employee_id')
            ->all();

        return Inertia::render('Owner/EmployeeShifts/Form', [
            'mode' => 'create',
            'employeeShift' => null,
            'employees' => $employees->map(fn (Employee $employee) => array_merge($this->employeeOption($employee), [
                'already_assigned' => in_array($employee->id, $assignedEmployeeIds),
            ]))->values(),
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

        // Cek apakah karyawan sudah punya penugasan shift aktif
        $existing = EmployeeShift::with('shift')
            ->where('employee_id', $request->employee_id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Karyawan sudah memiliki penugasan shift aktif: ' . ($existing->shift->name ?? 'Shift'))
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
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOption($employee))->values(),
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
            $existing = EmployeeShift::with('shift')
                ->where('employee_id', $request->employee_id)
                ->where('status', 'active')
                ->whereKeyNot($employeeShift->id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->with('error', 'Karyawan sudah memiliki penugasan shift aktif: ' . ($existing->shift->name ?? 'Shift'))
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
