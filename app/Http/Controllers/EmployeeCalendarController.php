<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\EmployeeShift;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeCalendarController extends Controller
{
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $calendar = $this->buildCalendar($employee, $request);

        return Inertia::render('Employee/Calendar/Index', $this->calendarPagePayload($employee, $calendar, route('employee.calendar.index')));
    }

    public function ownerShow(Request $request, Employee $employee)
    {
        $employee->load('user');
        $calendar = $this->buildCalendar($employee, $request);

        return Inertia::render('Owner/Employees/Calendar', $this->calendarPagePayload($employee, $calendar, route('owner.employees.calendar', $employee)));
    }

    // Get calendar data for AJAX (optional)
    public function getData(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $shifts = EmployeeShift::with('shift')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->get();

        $holidays = EmployeeHoliday::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['scheduled', 'taken'])
            ->get();

        $approvedLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', '<=', $year)
            ->whereYear('end_date', '>=', $year)
            ->get();

        return response()->json([
            'shifts' => $shifts,
            'holidays' => $holidays,
            'approved_leaves' => $approvedLeaves,
        ]);
    }

    private function buildCalendar(Employee $employee, Request $request): array
    {
        $targetMonth = Carbon::createFromDate(
            (int) ($request->year ?? now()->year),
            (int) ($request->month ?? now()->month),
            1
        );

        $month = $targetMonth->month;
        $year = $targetMonth->year;

        $shifts = EmployeeShift::with('shift')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereHas('shift', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        $holidays = EmployeeHoliday::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['scheduled', 'taken'])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $attendances = Attendance::with('shift')
            ->where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $approvedLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $targetMonth->copy()->endOfMonth()->toDateString())
            ->whereDate('end_date', '>=', $targetMonth->copy()->startOfMonth()->toDateString())
            ->get();

        foreach ($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dateStr = $date->format('Y-m-d');

                if ($date->year == $year && $date->month == $month && !isset($holidays[$dateStr])) {
                    $holidays[$dateStr] = (object) [
                        'date' => $date->copy(),
                        'reason' => $leave->reason ?? 'Cuti Disetujui',
                        'type' => $leave->leave_type ?? 'annual',
                        'status' => 'approved',
                    ];
                }
            }
        }

        $daysInMonth = $targetMonth->daysInMonth;
        $firstDayOfMonth = $targetMonth->dayOfWeek;
        $monthName = $targetMonth->format('F Y');
        $today = Carbon::now()->format('Y-m-d');
        $calendarData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateStr = $date->format('Y-m-d');
            $dayName = strtolower($date->format('l'));
            $shiftForDay = $this->findShiftForDate($shifts, $date, $dayName);
            $isHoliday = isset($holidays[$dateStr]);
            $holidayData = $isHoliday ? $holidays[$dateStr] : null;
            $attendance = $attendances[$dateStr] ?? null;

            $calendarData[$dateStr] = [
                'date' => $dateStr,
                'day' => $day,
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => $isHoliday,
                'holiday' => $holidayData ? $this->holidayPayload($holidayData) : null,
                'attendance' => $attendance ? $this->attendancePayload($attendance) : null,
                'attendance_status' => $attendance?->status,
                'is_absent' => $attendance?->status === 'absent',
                'is_present' => $attendance?->status === 'present',
                'is_late' => $attendance?->status === 'late',
                'shift' => $shiftForDay ? $this->shiftPayload($shiftForDay->shift) : null,
                'employee_shift' => $shiftForDay ? $this->employeeShiftPayload($shiftForDay) : null,
                'is_today' => $dateStr === $today,
                'is_past' => $date->isPast(),
                'holiday_type' => $isHoliday ? ($holidayData->status ?? 'scheduled') : null,
            ];
        }

        return [
            'calendarData' => $calendarData,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'daysInMonth' => $daysInMonth,
            'firstDayOfMonth' => $firstDayOfMonth,
            'today' => $today,
            'shifts' => $shifts,
            'holidays' => $holidays,
            'attendances' => $attendances,
            'stats' => $this->calculateStats($calendarData, $daysInMonth),
        ];
    }

    private function calendarPagePayload(Employee $employee, array $calendar, string $indexUrl): array
    {
        $calendarData = array_values($calendar['calendarData']);
        unset($calendar['calendarData'], $calendar['shifts'], $calendar['holidays'], $calendar['attendances']);

        return array_merge($calendar, [
            'employee' => $this->employeePayload($employee),
            'calendarData' => $calendarData,
            'links' => [
                'index' => $indexUrl,
                'today' => $indexUrl . '?' . http_build_query([
                    'month' => now()->month,
                    'year' => now()->year,
                ]),
                'previous' => $indexUrl . '?' . http_build_query([
                    'month' => Carbon::createFromDate($calendar['year'], $calendar['month'], 1)->subMonth()->month,
                    'year' => Carbon::createFromDate($calendar['year'], $calendar['month'], 1)->subMonth()->year,
                ]),
                'next' => $indexUrl . '?' . http_build_query([
                    'month' => Carbon::createFromDate($calendar['year'], $calendar['month'], 1)->addMonth()->month,
                    'year' => Carbon::createFromDate($calendar['year'], $calendar['month'], 1)->addMonth()->year,
                ]),
            ],
        ]);
    }

    private function employeePayload(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $employee->user?->name,
            'email' => $employee->user?->email,
            'employee_code' => $employee->employee_code,
            'position' => $employee->position,
            'status' => $employee->status,
        ];
    }

    private function shiftPayload($shift): ?array
    {
        if (!$shift) {
            return null;
        }

        return [
            'id' => $shift->id,
            'name' => $shift->name,
            'start_time' => Carbon::parse($shift->start_time)->format('H:i'),
            'end_time' => Carbon::parse($shift->end_time)->format('H:i'),
        ];
    }

    private function employeeShiftPayload(EmployeeShift $employeeShift): array
    {
        return [
            'id' => $employeeShift->id,
            'is_recurring' => (bool) $employeeShift->is_recurring,
            'day_of_week' => $employeeShift->day_of_week,
        ];
    }

    private function holidayPayload($holiday): array
    {
        $typeLabels = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Cuti Sakit',
            'personal' => 'Cuti Pribadi',
            'company' => 'Libur Perusahaan',
            'other' => 'Lainnya',
        ];

        return [
            'date' => Carbon::parse($holiday->date)->format('Y-m-d'),
            'reason' => $holiday->reason,
            'type' => $holiday->type ?? 'annual',
            'type_label' => method_exists($holiday, 'getTypeLabel') ? $holiday->getTypeLabel() : ($typeLabels[$holiday->type ?? 'annual'] ?? 'Cuti'),
            'status' => $holiday->status ?? 'scheduled',
        ];
    }

    private function attendancePayload(Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'status' => $attendance->status,
            'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : null,
            'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : null,
            'notes' => $attendance->notes,
        ];
    }

    private function findShiftForDate($shifts, Carbon $date, string $dayName): ?EmployeeShift
    {
        foreach ($shifts as $employeeShift) {
            if ($employeeShift->start_date && $date->lt($employeeShift->start_date)) {
                continue;
            }

            if ($employeeShift->end_date && $date->gt($employeeShift->end_date)) {
                continue;
            }

            if ($employeeShift->day_of_week && $employeeShift->day_of_week !== $dayName) {
                continue;
            }

            if ($employeeShift->is_recurring || (!$employeeShift->start_date && !$employeeShift->end_date)) {
                return $employeeShift;
            }

            if (!$employeeShift->is_recurring && $employeeShift->start_date && $employeeShift->end_date) {
                return $employeeShift;
            }
        }

        return null;
    }

    private function calculateStats(array $calendarData, int $daysInMonth): array
    {
        $totalWorkingDays = 0;
        $totalHolidays = 0;
        $totalWeekends = 0;
        $totalAbsentDays = 0;
        $totalPresentDays = 0;
        $totalLateDays = 0;

        foreach ($calendarData as $data) {
            if ($data['is_holiday']) {
                $totalHolidays++;
            } elseif ($data['shift']) {
                $totalWorkingDays++;
            }

            if ($data['is_absent']) {
                $totalAbsentDays++;
            }

            if ($data['is_present']) {
                $totalPresentDays++;
            }

            if ($data['is_late']) {
                $totalLateDays++;
            }

            if ($data['is_weekend']) {
                $totalWeekends++;
            }
        }

        return [
            'total_days' => $daysInMonth,
            'working_days' => $totalWorkingDays,
            'holidays' => $totalHolidays,
            'present_days' => $totalPresentDays,
            'late_days' => $totalLateDays,
            'absent_days' => $totalAbsentDays,
            'weekends' => $totalWeekends,
        ];
    }
}
