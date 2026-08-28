<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        return view('owner.employee-shifts.index', compact('employeeShifts', 'employees', 'shifts', 'stats'));
    }

    // Form tambah shift karyawan
    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        return view('owner.employee-shifts.create', compact('employees', 'shifts'));
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

        // Cek apakah karyawan sudah punya shift ini
        $existing = EmployeeShift::where('employee_id', $request->employee_id)
            ->where('shift_id', $request->shift_id)
            ->where('status', 'active')
            ->when($request->day_of_week, function($query) use ($request) {
                return $query->where('day_of_week', $request->day_of_week);
            })
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Karyawan sudah memiliki shift ini.')->withInput();
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
        return view('owner.employee-shifts.edit', compact('employeeShift', 'employees', 'shifts'));
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
}