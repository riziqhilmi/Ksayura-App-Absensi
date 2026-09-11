<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with([
            'user',
            'attendances' => function ($query) {
                $query->whereDate('date', today())->latest();
            },
        ])->get();
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('status', 'active')->count();
        
        return Inertia::render('Owner/Employees/Index', [
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'stats' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'average_daily_rate' => round((float) ($employees->avg('daily_rate') ?? 0)),
            ],
            'links' => [
                'create' => route('owner.employees.create'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Owner/Employees/Form', [
            'mode' => 'create',
            'employee' => null,
            'links' => [
                'index' => route('owner.employees.index'),
                'submit' => route('owner.employees.store'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:100',
            'daily_rate' => 'required|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create User Account
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'phone' => $request->phone,
            'address' => $request->address,
            'hire_date' => $request->hire_date,
        ]);

        // Create Employee Profile
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT),
            'position' => $request->position,
            'base_salary' => $request->daily_rate, // Simpan daily_rate sebagai base_salary juga
            'salary_type' => 'daily', // Default daily
            'daily_rate' => $request->daily_rate,
            'hourly_rate' => $request->hourly_rate,
            'status' => 'active',
        ]);

        return redirect()->route('owner.employees.index')->with('success', 'Karyawan berhasil ditambahkan! Akun login telah dibuat.');
    }

    public function show(Employee $employee)
    {
        $employee->load('user');
        return Inertia::render('Owner/Employees/Show', [
            'employee' => $this->employeePayload($employee),
            'links' => [
                'index' => route('owner.employees.index'),
                'edit' => route('owner.employees.edit', $employee),
            ],
        ]);
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        return Inertia::render('Owner/Employees/Form', [
            'mode' => 'edit',
            'employee' => $this->employeePayload($employee),
            'links' => [
                'index' => route('owner.employees.index'),
                'submit' => route('owner.employees.update', $employee),
            ],
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:100',
            'daily_rate' => 'required|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,resigned',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update User
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'hire_date' => $request->hire_date,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $employee->user->update($userData);

        // Update Employee
        $employee->update([
            'position' => $request->position,
            'base_salary' => $request->daily_rate,
            'salary_type' => 'daily',
            'daily_rate' => $request->daily_rate,
            'hourly_rate' => $request->hourly_rate,
            'status' => $request->status,
        ]);

        return redirect()->route('owner.employees.index')->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        $employee->delete();

        return redirect()->route('owner.employees.index')->with('success', 'Karyawan berhasil dihapus!');
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,resigned',
        ]);

        $employee->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status karyawan berhasil diperbarui']);
    }

    public function resetPassword(Request $request, Employee $employee)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $employee->user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }

    private function employeePayload(Employee $employee): array
    {
        $todayAttendance = $employee->attendances?->first();

        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'position' => $employee->position,
            'daily_rate' => $employee->daily_rate,
            'hourly_rate' => $employee->hourly_rate,
            'status' => $employee->status,
            'user' => [
                'id' => $employee->user?->id,
                'name' => $employee->user?->name,
                'email' => $employee->user?->email,
                'phone' => $employee->user?->phone,
                'address' => $employee->user?->address,
                'hire_date' => $employee->user?->hire_date ? Carbon::parse($employee->user->hire_date)->format('Y-m-d') : null,
                'hire_date_label' => $employee->user?->hire_date ? Carbon::parse($employee->user->hire_date)->format('d F Y') : '-',
            ],
            'today_attendance' => $todayAttendance ? [
                'status' => $todayAttendance->status,
                'check_in_time' => optional($todayAttendance->check_in_time)->format('H:i'),
                'check_out_time' => optional($todayAttendance->check_out_time)->format('H:i'),
            ] : null,
            'urls' => [
                'show' => route('owner.employees.show', $employee),
                'edit' => route('owner.employees.edit', $employee),
                'calendar' => route('owner.employees.calendar', $employee),
                'destroy' => route('owner.employees.destroy', $employee),
                'update_status' => route('owner.employees.update-status', $employee),
                'reset_password' => route('owner.employees.reset-password', $employee),
            ],
        ];
    }
}
