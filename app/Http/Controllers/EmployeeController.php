<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

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
        
        return view('owner.employees.index', compact('employees', 'totalEmployees', 'activeEmployees'));
    }

    public function create()
    {
        return view('owner.employees.create');
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
        return view('owner.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        return view('owner.employees.edit', compact('employee'));
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
}
