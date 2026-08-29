<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\EmployeeShift;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeCalendarController extends Controller
{
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $calendar = $this->buildCalendar($employee, $request);

        return view('employee.calendar.index', array_merge(
            ['employee' => $employee],
            $calendar
        ));
    }

    public function ownerShow(Request $request, Employee $employee)
    {
        $employee->load('user');
        $calendar = $this->buildCalendar($employee, $request);

        return view('owner.employees.calendar', array_merge(
            ['employee' => $employee],
            $calendar
        ));
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

            $calendarData[$dateStr] = [
                'date' => $dateStr,
                'day' => $day,
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => $isHoliday,
                'holiday' => $holidayData,
                'shift' => $shiftForDay ? $shiftForDay->shift : null,
                'employee_shift' => $shiftForDay,
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
            'stats' => $this->calculateStats($calendarData, $daysInMonth),
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

        foreach ($calendarData as $data) {
            if ($data['is_holiday']) {
                $totalHolidays++;
            } elseif ($data['is_weekend']) {
                $totalWeekends++;
            } elseif ($data['shift']) {
                $totalWorkingDays++;
            }
        }

        return [
            'total_days' => $daysInMonth,
            'working_days' => $totalWorkingDays,
            'holidays' => $totalHolidays,
            'weekends' => $totalWeekends,
        ];
    }
}
