<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    // ==================== OWNER METHODS ====================
    
    // Owner: Melihat semua absensi
    public function index(Request $request)
    {
        $query = Attendance::with(['employee.user', 'shift']);
        
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            $query->whereDate('date', today());
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest('date')->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        $stats = [
            'total' => Attendance::whereDate('date', today())->count(),
            'present' => Attendance::whereDate('date', today())->where('status', 'present')->count(),
            'late' => Attendance::whereDate('date', today())->where('status', 'late')->count(),
            'absent' => Attendance::whereDate('date', today())->where('status', 'absent')->count(),
            'leave' => Attendance::whereDate('date', today())->where('status', 'leave')->count(),
            'half_day' => Attendance::whereDate('date', today())->where('status', 'half_day')->count(),
            'auto_checkout' => Attendance::whereDate('date', today())->where('status', 'auto_checkout')->count(),
        ];

        return view('owner.attendance.index', compact('attendances', 'employees', 'stats'));
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.user', 'shift']);
        return view('owner.attendance.show', compact('attendance'));
    }

    public function updateStatus(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day,leave,auto_checkout',
            'notes' => 'nullable|string'
        ]);

        $attendance->update([
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status absensi berhasil diperbarui'
        ]);
    }

    public function export(Request $request)
    {
        return redirect()->back()->with('info', 'Fitur export akan segera tersedia');
    }

    // ==================== EMPLOYEE METHODS ====================

    public function myAttendance(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if ($todayAttendance && $todayAttendance->shift_id) {
            $todayShift = Shift::find($todayAttendance->shift_id);
        } else {
            $todayShift = $this->getEmployeeShiftForDate($employee, today());
        }

        $query = Attendance::where('employee_id', $employee->id);
        
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        } else {
            $query->whereMonth('date', now()->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        } else {
            $query->whereYear('date', now()->year);
        }

        $attendances = $query->latest('date')->paginate(20);
        
        $stats = [
            'total' => $query->count(),
            'present' => $query->where('status', 'present')->count(),
            'late' => $query->where('status', 'late')->count(),
            'absent' => $query->where('status', 'absent')->count(),
            'leave' => $query->where('status', 'leave')->count(),
            'half_day' => $query->where('status', 'half_day')->count(),
            'auto_checkout' => $query->where('status', 'auto_checkout')->count(),
        ];

        $officeLocation = CompanySetting::getOfficeLocation();

        return view('employee.attendance.index', compact(
            'attendances', 
            'stats', 
            'todayAttendance', 
            'todayShift',
            'officeLocation'
        ));
    }

    public function checkIn(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        $existing = Attendance::where('employee_id', $employee->id)
                              ->whereDate('date', today())
                              ->first();

        if ($existing && $existing->check_in_time) {
            return response()->json(['error' => 'Anda sudah melakukan check in hari ini'], 400);
        }

        // Cek apakah hari ini libur
        $holiday = $this->getEmployeeHolidayForDate($employee, today());
        if ($holiday) {
            return response()->json([
                'error' => 'Anda tidak dapat melakukan check in karena hari ini adalah jadwal libur Anda',
                'is_holiday' => true,
                'holiday' => [
                    'reason' => $holiday->reason,
                    'type' => $holiday->getTypeLabel(),
                ],
            ], 400);
        }

        // Get shift untuk hari ini
        $shift = $this->getEmployeeShiftForDate($employee, today());
        $now = Carbon::now();

        if (!$shift) {
            return response()->json([
                'error' => 'Anda belum diberi shift. Silakan hubungi owner/admin.',
                'status' => 'no_shift',
                'can_check_in' => false,
            ], 400);
        }
        
        // Cek apakah masih dalam jam shift (maksimal 2 jam setelah shift berakhir)
        $shiftEnd = $this->getShiftEndDateTime($now, $shift);
        $maxCheckInTime = $shiftEnd->copy()->addHours(2);
        
        if ($now->greaterThan($maxCheckInTime)) {
            // Tandai sebagai tidak hadir
            Attendance::create([
                'employee_id' => $employee->id,
                'shift_id' => $shift->id,
                'date' => today(),
                'status' => 'absent',
                'notes' => 'Tidak hadir (melewati batas waktu check in)',
            ]);
            
            return response()->json([
                'error' => 'Anda sudah melewati batas waktu check in',
                'status' => 'absent',
                'max_check_in_time' => $maxCheckInTime->format('H:i'),
            ], 400);
        }

        // Cek window check in (minimal 2 jam sebelum shift)
        $checkInWindow = $this->getCheckInWindow($now, $shift);
        if (!$checkInWindow['can_check_in']) {
            return response()->json([
                'error' => 'Anda masih belum bisa check in',
                'available_from' => $checkInWindow['available_from'],
                'shift_start_time' => $checkInWindow['shift_start_time'],
                'can_check_in' => false,
            ], 400);
        }

        // Validasi lokasi
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isWithinRadius = CompanySetting::isWithinOfficeRadius(
            $request->latitude,
            $request->longitude
        );

        if (!$isWithinRadius) {
            $office = CompanySetting::getOfficeLocation();
            $distance = CompanySetting::calculateDistance(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $office['latitude'],
                (float) $office['longitude']
            );
            
            return response()->json([
                'error' => 'Anda berada di luar radius kantor',
                'distance' => round($distance),
                'max_distance' => $office['radius']
            ], 400);
        }

        $status = $this->determineAttendanceStatus($now, $shift);
        $lateMinutes = $this->getLateMinutes($now, $shift);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift ? $shift->id : null,
            'date' => today(),
            'check_in_time' => $now,
            'latitude_in' => $request->latitude,
            'longitude_in' => $request->longitude,
            'check_in_location' => $request->location_name ?? 'Check In',
            'status' => $status,
            'is_auto_checkout' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check in berhasil',
            'data' => $attendance,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'late_text' => $lateMinutes > 0 ? $this->formatDuration($lateMinutes) : null,
        ]);
    }

    public function checkOut(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
                                ->whereDate('date', today())
                                ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Anda belum melakukan check in'], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(['error' => 'Anda sudah melakukan check out hari ini'], 400);
        }

        // Validasi lokasi
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isWithinRadius = CompanySetting::isWithinOfficeRadius(
            $request->latitude,
            $request->longitude
        );

        if (!$isWithinRadius) {
            $office = CompanySetting::getOfficeLocation();
            $distance = CompanySetting::calculateDistance(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $office['latitude'],
                (float) $office['longitude']
            );
            
            return response()->json([
                'error' => 'Anda berada di luar radius kantor',
                'distance' => round($distance),
                'max_distance' => $office['radius']
            ], 400);
        }

        $checkInTime = Carbon::parse($attendance->check_in_time);
        $checkOutTime = Carbon::now();
        $workDuration = $checkInTime->diffInMinutes($checkOutTime);
        
        if ($workDuration < 240 && $attendance->status != 'leave') {
            $attendance->status = 'half_day';
        }

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'latitude_out' => $request->latitude,
            'longitude_out' => $request->longitude,
            'check_out_location' => $request->location_name ?? 'Check Out',
            'status' => $attendance->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check out berhasil',
            'data' => $attendance,
            'work_duration' => $workDuration,
            'work_duration_text' => $this->formatDuration($workDuration)
        ]);
    }

    public function getTodayStatus()
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
                                ->whereDate('date', today())
                                ->first();

        $shift = $attendance && $attendance->shift_id
            ? Shift::find($attendance->shift_id)
            : $this->getEmployeeShiftForDate($employee, today());
        
        $officeLocation = CompanySetting::getOfficeLocation();
        $holiday = $this->getEmployeeHolidayForDate($employee, today());
        $now = Carbon::now();
        $checkInWindow = $this->getCheckInWindow($now, $shift);
        
        // Cek apakah sudah melewati batas waktu check in (2 jam setelah shift berakhir)
        $isPastCheckInTime = false;
        $maxCheckInTime = null;
        $shiftEndTime = null;
        
        if ($shift && !$attendance) {
            $shiftEnd = $this->getShiftEndDateTime($now, $shift);
            $maxCheckInTime = $shiftEnd->copy()->addHours(2);
            $isPastCheckInTime = $now->greaterThan($maxCheckInTime);
            $shiftEndTime = $shiftEnd->format('H:i');
        }

        if (!$attendance) {
            if (!$shift) {
                return response()->json([
                    'checked_in' => false,
                    'checked_out' => false,
                    'status' => 'no_shift',
                    'can_check_in' => false,
                    'message' => 'Anda belum diberi shift. Silakan hubungi owner/admin.',
                    'shift' => null,
                    'office_location' => $officeLocation
                ]);
            }

            if ($holiday) {
                return response()->json([
                    'checked_in' => false,
                    'checked_out' => false,
                    'status' => 'holiday',
                    'can_check_in' => false,
                    'holiday' => [
                        'reason' => $holiday->reason,
                        'type' => $holiday->getTypeLabel(),
                    ],
                    'shift' => $shift ? [
                        'name' => $shift->name,
                        'start_time' => date('H:i', strtotime($shift->start_time)),
                        'end_time' => date('H:i', strtotime($shift->end_time)),
                        'grace_period' => $shift->grace_period
                    ] : null,
                    'office_location' => $officeLocation
                ]);
            }

            if ($isPastCheckInTime) {
                return response()->json([
                    'checked_in' => false,
                    'checked_out' => false,
                    'status' => 'past_check_in',
                    'can_check_in' => false,
                    'is_past_check_in' => true,
                    'max_check_in_time' => $maxCheckInTime->format('H:i'),
                    'shift_end_time' => $shiftEndTime,
                    'shift' => $shift ? [
                        'name' => $shift->name,
                        'start_time' => date('H:i', strtotime($shift->start_time)),
                        'end_time' => date('H:i', strtotime($shift->end_time)),
                        'grace_period' => $shift->grace_period
                    ] : null,
                    'office_location' => $officeLocation
                ]);
            }

            return response()->json([
                'checked_in' => false,
                'checked_out' => false,
                'status' => 'not_started',
                'can_check_in' => $checkInWindow['can_check_in'],
                'available_from' => $checkInWindow['available_from'],
                'shift_start_time' => $checkInWindow['shift_start_time'],
                'shift' => $shift ? [
                    'name' => $shift->name,
                    'start_time' => date('H:i', strtotime($shift->start_time)),
                    'end_time' => date('H:i', strtotime($shift->end_time)),
                    'grace_period' => $shift->grace_period
                ] : null,
                'office_location' => $officeLocation
            ]);
        }

        $response = [
            'checked_in' => !is_null($attendance->check_in_time),
            'checked_out' => !is_null($attendance->check_out_time),
            'check_in_time' => $attendance->check_in_time ? date('H:i', strtotime($attendance->check_in_time)) : null,
            'check_out_time' => $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : null,
            'status' => $attendance->status,
            'attendance_id' => $attendance->id,
            'is_auto_checkout' => $attendance->is_auto_checkout ?? false,
            'late_minutes' => $attendance->check_in_time
                ? $this->getLateMinutes(Carbon::parse($attendance->check_in_time), $shift)
                : 0,
            'shift' => $shift ? [
                'name' => $shift->name,
                'start_time' => date('H:i', strtotime($shift->start_time)),
                'end_time' => date('H:i', strtotime($shift->end_time)),
                'grace_period' => $shift->grace_period
            ] : null,
            'office_location' => $officeLocation
        ];

        if ($attendance->check_in_time && $attendance->check_out_time) {
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);
            $response['work_duration'] = $checkIn->diffInMinutes($checkOut);
            $response['work_duration_text'] = $this->formatDuration($checkIn->diffInMinutes($checkOut));
        }

        return response()->json($response);
    }

    // ==================== AUTO CHECK OUT ====================

    // Auto Check Out untuk karyawan yang lupa
    public function autoCheckOut()
    {
        $today = today();
        $now = Carbon::now();
        
        // Get all attendances that are checked in but not checked out
        $attendances = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->where('status', '!=', 'leave')
            ->get();

        $count = 0;

        foreach ($attendances as $attendance) {
            $shift = $attendance->shift;
            if (!$shift) continue;

            $shiftEnd = $this->getShiftEndDateTime($now, $shift);
            $autoCheckOutTime = $shiftEnd->copy()->addMinutes(15);

            // Jika sekarang sudah melewati 15 menit setelah shift berakhir
            if ($now->greaterThanOrEqualTo($autoCheckOutTime)) {
                $checkInTime = Carbon::parse($attendance->check_in_time);
                $workDuration = $checkInTime->diffInMinutes($now);
                
                $status = 'auto_checkout';
                if ($workDuration < 240) {
                    $status = 'half_day';
                }

                $attendance->update([
                    'check_out_time' => $now,
                    'status' => $status,
                    'is_auto_checkout' => true,
                    'notes' => 'Auto check out - Karyawan lupa check out',
                ]);
                
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto check out berhasil dilakukan untuk ' . $count . ' karyawan',
            'count' => $count
        ]);
    }

    // ==================== HELPER METHODS ====================

    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . ' jam ' . $mins . ' menit';
    }

    private function getEmployeeShiftForDate(Employee $employee, $date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = strtolower($date->format('l'));

        $employeeShift = EmployeeShift::with('shift')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereHas('shift', function ($query) {
                $query->where('status', 'active');
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            })
            ->where(function ($query) use ($dayOfWeek) {
                $query->whereNull('day_of_week')
                    ->orWhere('day_of_week', $dayOfWeek);
            })
            ->orderByRaw('CASE WHEN day_of_week IS NULL THEN 1 ELSE 0 END')
            ->latest('start_date')
            ->latest()
            ->first();

        return $employeeShift?->shift;
    }

    private function getEmployeeHolidayForDate(Employee $employee, $date)
    {
        return EmployeeHoliday::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::parse($date)->toDateString())
            ->whereIn('status', ['scheduled', 'taken'])
            ->first();
    }

    private function getCheckInWindow(Carbon $now, $shift)
    {
        if (!$shift) {
            return [
                'can_check_in' => false,
                'available_from' => null,
                'shift_start_time' => null,
            ];
        }

        $shiftStart = $this->getShiftStartDateTime($now, $shift);
        $availableFrom = $shiftStart->copy()->subHours(2);

        return [
            'can_check_in' => $now->greaterThanOrEqualTo($availableFrom),
            'available_from' => $availableFrom->format('H:i'),
            'shift_start_time' => $shiftStart->format('H:i'),
        ];
    }

    private function getLateMinutes(Carbon $checkInTime, $shift)
    {
        if (!$shift) {
            return 0;
        }

        $shiftStart = $this->getShiftStartDateTime($checkInTime, $shift);
        $lateThreshold = $shiftStart->copy()->addMinutes((int) ($shift->grace_period ?? 15));

        return $checkInTime->greaterThan($lateThreshold)
            ? $lateThreshold->diffInMinutes($checkInTime)
            : 0;
    }

    private function getShiftStartDateTime(Carbon $date, $shift)
    {
        return $date->copy()->setTimeFromTimeString(date('H:i:s', strtotime($shift->start_time)));
    }

    private function getShiftEndDateTime(Carbon $date, $shift)
    {
        return $date->copy()->setTimeFromTimeString(date('H:i:s', strtotime($shift->end_time)));
    }

    private function determineAttendanceStatus($checkInTime, $shift)
    {
        if (!$shift) {
            return 'present';
        }

        $shiftStart = $this->getShiftStartDateTime($checkInTime, $shift);
        $lateThreshold = $shiftStart->copy()->addMinutes((int) ($shift->grace_period ?? 15));

        return $checkInTime->greaterThan($lateThreshold) ? 'late' : 'present';
    }
}
