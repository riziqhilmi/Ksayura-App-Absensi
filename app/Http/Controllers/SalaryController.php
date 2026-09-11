<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

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

        $salaries = $query->latest()->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        // Stats
        $stats = [
            'total' => $query->count(),
            'draft' => Salary::where('period', now()->format('Y-m'))->where('status', 'draft')->count(),
            'calculated' => Salary::where('period', now()->format('Y-m'))->where('status', 'calculated')->count(),
            'paid' => Salary::where('period', now()->format('Y-m'))->where('status', 'paid')->count(),
            'total_amount' => Salary::where('period', now()->format('Y-m'))->where('status', 'paid')->sum('total_salary'),
        ];

        return Inertia::render('Owner/Salaries/Index', [
            'salaries' => $salaries->through(fn (Salary $salary) => $this->salaryPayload($salary)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOption($employee))->values(),
            'stats' => $stats,
            'filters' => [
                'period' => $request->input('period', now()->format('Y-m')),
                'employee' => $request->input('employee', ''),
                'status' => $request->input('status', ''),
            ],
            'options' => [
                'periods' => collect(range(0, 11))->map(function ($offset) {
                    $date = now()->subMonths($offset);

                    return [
                        'value' => $date->format('Y-m'),
                        'label' => $date->format('F Y'),
                    ];
                })->values(),
            ],
            'links' => [
                'index' => route('owner.salaries.index'),
                'calculate' => route('owner.salaries.calculate'),
                'export' => route('owner.salaries.export'),
            ],
        ]);
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

                if ($existingSalary && $existingSalary->status === 'paid') {
                    continue;
                }

                // Get attendance data
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $workingDays = $endDate->diffInDays($startDate) + 1;
                $presentDays = $attendances->where('status', 'present')->count();
                $lateDays = $attendances->where('status', 'late')->count();
                $halfDayDays = $attendances->where('status', 'half_day')->count();
                $autoCheckoutDays = $attendances->where('status', 'auto_checkout')->count();
                $absentDays = $attendances->where('status', 'absent')->count();
                $leaveDays = $attendances->where('status', 'leave')->count();
                $holidayDays = EmployeeHoliday::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled')
                    ->count();

                $dailyRate = (float) ($employee->daily_rate ?? $employee->base_salary ?? 0);
                $paidDays = $presentDays + $lateDays + $autoCheckoutDays + ($halfDayDays * 0.5);
                $baseSalary = $dailyRate * $paidDays;
                $overtimeHours = 0;
                $overtimePay = 0;
                $attendanceBonus = 0;
                $deductions = 0;
                $totalSalary = $baseSalary;

                // Create or update salary
                Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period' => $period,
                    ],
                    [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'daily_rate' => $dailyRate,
                        'paid_days' => $paidDays,
                        'holiday_days' => $holidayDays,
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
        return Inertia::render('Owner/Salaries/Show', [
            'salary' => $this->salaryPayload($salary),
            'links' => [
                'index' => route('owner.salaries.index'),
            ],
        ]);
    }

    // Owner: Edit salary
    public function edit(Salary $salary)
    {
        if ($salary->status === 'paid') {
            return redirect()->route('owner.salaries.index')
                ->with('error', 'Gaji yang sudah dibayar tidak dapat diedit');
        }

        $salary->load(['employee.user']);
        return Inertia::render('Owner/Salaries/Edit', [
            'salary' => $this->salaryPayload($salary),
            'links' => [
                'show' => route('owner.salaries.show', $salary),
                'update' => route('owner.salaries.update', $salary),
            ],
        ]);
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

        $statsQuery = clone $query;
        $salaries = $query->latest()->paginate(20)->withQueryString();
        
        // Stats
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->sum('total_salary'),
            'average' => (clone $statsQuery)->where('status', 'paid')->avg('total_salary') ?? 0,
        ];

        return Inertia::render('Employee/Salaries/Index', [
            'salaries' => $salaries->through(fn (Salary $salary) => $this->salaryPayload($salary)),
            'stats' => $stats,
            'filters' => [
                'period' => $request->input('period', ''),
            ],
            'options' => [
                'periods' => collect(range(0, 11))->map(function ($offset) {
                    $date = now()->subMonths($offset);

                    return [
                        'value' => $date->format('Y-m'),
                        'label' => $date->format('F Y'),
                    ];
                })->values(),
            ],
            'links' => [
                'index' => route('employee.salaries.my'),
            ],
        ]);
    }

    private function salaryPayload(Salary $salary): array
    {
        $paidDays = (float) ($salary->paid_days ?? ($salary->present_days + $salary->late_days));
        $dailyRate = (float) ($salary->daily_rate ?? $salary->employee?->daily_rate ?? 0);
        $attendancePercentage = $salary->working_days > 0
            ? round(($paidDays / $salary->working_days) * 100)
            : 0;

        return [
            'id' => $salary->id,
            'period' => $salary->period,
            'start_date' => optional($salary->start_date)->format('Y-m-d'),
            'start_date_label' => optional($salary->start_date)->format('d F Y'),
            'end_date' => optional($salary->end_date)->format('Y-m-d'),
            'end_date_label' => optional($salary->end_date)->format('d F Y'),
            'employee' => $salary->employee ? $this->employeeOption($salary->employee) : null,
            'daily_rate' => $dailyRate,
            'paid_days' => $paidDays,
            'base_salary' => (float) $salary->base_salary,
            'overtime_hours' => (float) $salary->overtime_hours,
            'overtime_pay' => (float) $salary->overtime_pay,
            'attendance_bonus' => (float) $salary->attendance_bonus,
            'performance_bonus' => (float) $salary->performance_bonus,
            'deductions' => (float) $salary->deductions,
            'total_salary' => (float) $salary->total_salary,
            'working_days' => (int) $salary->working_days,
            'present_days' => (int) $salary->present_days,
            'late_days' => (int) $salary->late_days,
            'absent_days' => (int) $salary->absent_days,
            'leave_days' => (int) $salary->leave_days,
            'holiday_days' => (int) ($salary->holiday_days ?? 0),
            'attendance_percentage' => $attendancePercentage,
            'status' => $salary->status,
            'paid_date' => optional($salary->paid_date)->format('d F Y H:i'),
            'notes' => $salary->notes,
            'urls' => [
                'show' => route('owner.salaries.show', $salary),
                'edit' => route('owner.salaries.edit', $salary),
                'update' => route('owner.salaries.update', $salary),
                'mark_paid' => route('owner.salaries.mark-paid', $salary),
                'destroy' => route('owner.salaries.destroy', $salary),
            ],
        ];
    }

    private function employeeOption(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'name' => $employee->user?->name,
            'email' => $employee->user?->email,
            'position' => $employee->position,
            'status' => $employee->status,
            'daily_rate' => (float) ($employee->daily_rate ?? 0),
        ];
    }

    // Employee: View salary detail
    public function myShow(Salary $salary)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee || $salary->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $salary->load(['employee.user']);
        return Inertia::render('Employee/Salaries/Show', [
            'salary' => $this->salaryPayload($salary),
            'links' => [
                'index' => route('employee.salaries.my'),
            ],
        ]);
    }
}
