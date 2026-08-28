<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalaryController extends Controller
{
    // Owner: Index semua gaji
    public function index(Request $request)
    {
        $query = Salary::with(['employee.user']);
        
        // Filter by period
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        } else {
            $query->where('period', now()->format('Y-m'));
        }

        // Filter by employee
        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaries = $query->latest()->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        // Stats
        $stats = [
            'total' => $query->count(),
            'draft' => Salary::where('period', now()->format('Y-m'))->where('status', 'draft')->count(),
            'calculated' => Salary::where('period', now()->format('Y-m'))->where('status', 'calculated')->count(),
            'paid' => Salary::where('period', now()->format('Y-m'))->where('status', 'paid')->count(),
            'total_amount' => Salary::where('period', now()->format('Y-m'))->where('status', 'paid')->sum('total_salary'),
        ];

        return view('owner.salaries.index', compact('salaries', 'employees', 'stats'));
    }

    // Owner: Calculate salary for a period
    public function calculate(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $period = $request->period;
        $employeeId = $request->employee_id;

        // Parse period
        $year = substr($period, 0, 4);
        $month = substr($period, 5, 2);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Get employees
        $employees = Employee::with('user')
            ->where('status', 'active')
            ->when($employeeId, function ($query) use ($employeeId) {
                return $query->where('id', $employeeId);
            })
            ->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada karyawan yang ditemukan');
        }

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Check if salary already exists
                $existingSalary = Salary::where('employee_id', $employee->id)
                    ->where('period', $period)
                    ->first();

                if ($existingSalary && $existingSalary->status !== 'draft') {
                    continue; // Skip if already calculated or paid
                }

                // Get attendance data
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $workingDays = $endDate->diffInDays($startDate) + 1;
                $presentDays = $attendances->where('status', 'present')->count();
                $lateDays = $attendances->where('status', 'late')->count();
                $absentDays = $attendances->where('status', 'absent')->count();
                $leaveDays = $attendances->where('status', 'leave')->count();

                // Calculate salary components
                $baseSalary = $employee->base_salary;
                
                // Calculate daily rate
                $dailyRate = $employee->daily_rate ?? ($baseSalary / $workingDays);
                
                // Calculate attendance bonus (example: 10% of base salary if attendance > 90%)
                $attendanceRate = $workingDays > 0 ? ($presentDays / $workingDays) * 100 : 0;
                $attendanceBonus = 0;
                if ($attendanceRate >= 90) {
                    $attendanceBonus = $baseSalary * 0.1;
                }

                // Calculate overtime (example: 1.5x hourly rate)
                $overtimeHours = 0; // Could be fetched from attendance
                $hourlyRate = $employee->hourly_rate ?? ($baseSalary / ($workingDays * 8));
                $overtimePay = $overtimeHours * $hourlyRate * 1.5;

                // Calculate deductions (example: late penalty)
                $deductions = $lateDays * ($dailyRate * 0.1);

                // Total salary
                $totalSalary = $baseSalary + $overtimePay + $attendanceBonus - $deductions;

                // Create or update salary
                Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period' => $period,
                    ],
                    [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'base_salary' => $baseSalary,
                        'overtime_hours' => $overtimeHours,
                        'overtime_pay' => $overtimePay,
                        'attendance_bonus' => $attendanceBonus,
                        'performance_bonus' => 0,
                        'deductions' => $deductions,
                        'total_salary' => $totalSalary,
                        'working_days' => $workingDays,
                        'present_days' => $presentDays,
                        'late_days' => $lateDays,
                        'absent_days' => $absentDays,
                        'leave_days' => $leaveDays,
                        'status' => 'calculated',
                    ]
                );
            }

            DB::commit();
            return redirect()->route('owner.salaries.index', ['period' => $period])
                ->with('success', 'Perhitungan gaji berhasil dilakukan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Owner: Mark salary as paid
    public function markPaid(Salary $salary)
    {
        if ($salary->status !== 'calculated') {
            return response()->json(['error' => 'Gaji harus dihitung terlebih dahulu'], 400);
        }

        $salary->update([
            'status' => 'paid',
            'paid_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gaji berhasil ditandai sebagai sudah dibayar'
        ]);
    }

    // Owner: Show salary detail
    public function show(Salary $salary)
    {
        $salary->load(['employee.user']);
        return view('owner.salaries.show', compact('salary'));
    }

    // Owner: Edit salary
    public function edit(Salary $salary)
    {
        if ($salary->status === 'paid') {
            return redirect()->route('owner.salaries.index')
                ->with('error', 'Gaji yang sudah dibayar tidak dapat diedit');
        }

        $salary->load(['employee.user']);
        return view('owner.salaries.edit', compact('salary'));
    }

    // Owner: Update salary manually
    public function update(Request $request, Salary $salary)
    {
        if ($salary->status === 'paid') {
            return redirect()->back()->with('error', 'Gaji yang sudah dibayar tidak dapat diedit');
        }

        $validator = Validator::make($request->all(), [
            'base_salary' => 'required|numeric|min:0',
            'overtime_pay' => 'required|numeric|min:0',
            'attendance_bonus' => 'required|numeric|min:0',
            'performance_bonus' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $totalSalary = $request->base_salary + $request->overtime_pay + 
                      $request->attendance_bonus + $request->performance_bonus - 
                      $request->deductions;

        $salary->update([
            'base_salary' => $request->base_salary,
            'overtime_pay' => $request->overtime_pay,
            'attendance_bonus' => $request->attendance_bonus,
            'performance_bonus' => $request->performance_bonus,
            'deductions' => $request->deductions,
            'total_salary' => $totalSalary,
            'notes' => $request->notes,
            'status' => 'calculated',
        ]);

        return redirect()->route('owner.salaries.show', $salary)
            ->with('success', 'Data gaji berhasil diperbarui');
    }

    // Owner: Delete salary draft
    public function destroy(Salary $salary)
    {
        if ($salary->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya gaji draft yang dapat dihapus');
        }

        $salary->delete();
        return redirect()->route('owner.salaries.index')
            ->with('success', 'Gaji draft berhasil dihapus');
    }

    // Owner: Export salary report
    public function export(Request $request)
    {
        // Implement export to Excel/PDF
        return redirect()->back()->with('info', 'Fitur export akan segera tersedia');
    }

    // Employee: View my salaries
    public function mySalaries(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $query = Salary::where('employee_id', $employee->id);
        
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $salaries = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => $query->count(),
            'paid' => $query->where('status', 'paid')->sum('total_salary'),
            'average' => $query->where('status', 'paid')->avg('total_salary') ?? 0,
        ];

        return view('employee.salaries.index', compact('salaries', 'stats'));
    }

    // Employee: View salary detail
    public function myShow(Salary $salary)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee || $salary->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $salary->load(['employee.user']);
        return view('employee.salaries.show', compact('salary'));
    }
}