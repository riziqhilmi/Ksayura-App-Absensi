<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Salary;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShiftAndRiziqAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $morningShift = Shift::updateOrCreate(
            ['name' => 'Shift Pagi'],
            [
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'grace_period' => 15,
                'status' => 'active',
                'notes' => 'Shift operasional pagi',
            ]
        );

        Shift::updateOrCreate(
            ['name' => 'Shift Siang'],
            [
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'break_start' => '18:00:00',
                'break_end' => '19:00:00',
                'grace_period' => 15,
                'status' => 'active',
                'notes' => 'Shift operasional siang',
            ]
        );

        Shift::updateOrCreate(
            ['name' => 'Shift Malam'],
            [
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'break_start' => '02:00:00',
                'break_end' => '03:00:00',
                'grace_period' => 15,
                'status' => 'active',
                'notes' => 'Shift operasional malam',
            ]
        );

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $user = User::updateOrCreate(
            ['email' => 'riziqhilmi17@gmail.com'],
            [
                'name' => 'Riziq Hilmi',
                'password' => Hash::make('12345678'),
                'role' => 'employee',
                'phone' => '081234567891',
                'address' => 'Jakarta',
                'hire_date' => $periodStart->toDateString(),
                'email_verified_at' => now(),
            ]
        );

        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => 'EMP-RIZIQ',
                'position' => 'Staff',
                'base_salary' => 80000,
                'salary_type' => 'daily',
                'daily_rate' => 80000,
                'hourly_rate' => 10000,
                'status' => 'active',
            ]
        );

        EmployeeShift::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'shift_id' => $morningShift->id,
                'day_of_week' => null,
            ],
            [
                'start_date' => $periodStart->toDateString(),
                'end_date' => null,
                'is_recurring' => true,
                'status' => 'active',
                'notes' => 'Jadwal default Riziq Hilmi',
            ]
        );

        for ($day = 0; $day < 28; $day++) {
            $date = $periodStart->copy()->addDays($day);

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'shift_id' => $morningShift->id,
                    'check_in_time' => '08:00:00',
                    'check_out_time' => '16:00:00',
                    'check_in_location' => 'Kantor Sayur',
                    'check_out_location' => 'Kantor Sayur',
                    'status' => 'present',
                    'notes' => 'Data hadir dari seeder',
                ]
            );
        }

        Salary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period' => $periodStart->format('Y-m'),
            ],
            [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'daily_rate' => 80000,
                'paid_days' => 28,
                'holiday_days' => 0,
                'base_salary' => 2240000,
                'overtime_hours' => 0,
                'overtime_pay' => 0,
                'attendance_bonus' => 0,
                'performance_bonus' => 0,
                'deductions' => 0,
                'total_salary' => 2240000,
                'working_days' => $periodEnd->day,
                'present_days' => 28,
                'late_days' => 0,
                'absent_days' => 0,
                'leave_days' => 0,
                'status' => 'calculated',
                'notes' => '28 hari kerja x Rp 80.000 dari seeder',
            ]
        );
    }
}
