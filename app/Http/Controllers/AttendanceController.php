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
use Inertia\Inertia;

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

        $attendances = $query->latest('date')->paginate(20)->withQueryString();
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

        return Inertia::render('Owner/Attendance/Index', [
            'attendances' => $attendances->through(fn (Attendance $attendance) => $this->attendancePayload($attendance)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOption($employee))->values(),
            'stats' => $stats,
            'filters' => [
                'date' => $request->input('date', today()->toDateString()),
                'employee' => $request->input('employee', ''),
                'status' => $request->input('status', ''),
            ],
            'links' => [
                'index' => route('owner.attendance.index'),
                'export' => route('owner.attendance.export'),
            ],
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.user', 'shift']);
        return Inertia::render('Owner/Attendance/Show', [
            'attendance' => $this->attendancePayload($attendance),
            'links' => [
                'index' => route('owner.attendance.index'),
                'updateStatus' => route('owner.attendance.update-status', $attendance),
            ],
        ]);
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

        $query = Attendance::with('shift')->where('employee_id', $employee->id);
        
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

        $statsQuery = clone $query;
        $attendances = $query->latest('date')->paginate(20)->withQueryString();
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'present' => (clone $statsQuery)->where('status', 'present')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
            'absent' => (clone $statsQuery)->where('status', 'absent')->count(),
            'leave' => (clone $statsQuery)->where('status', 'leave')->count(),
            'half_day' => (clone $statsQuery)->where('status', 'half_day')->count(),
            'auto_checkout' => (clone $statsQuery)->where('status', 'auto_checkout')->count(),
        ];

        $officeLocation = CompanySetting::getOfficeLocation();

        return Inertia::render('Employee/Attendance/Index', [
            'attendances' => $attendances->through(fn (Attendance $attendance) => $this->attendancePayload($attendance)),
            'stats' => $stats,
            'todayAttendance' => $todayAttendance ? $this->attendancePayload($todayAttendance->loadMissing('shift')) : null,
            'todayShift' => $todayShift ? $this->shiftPayload($todayShift) : null,
            'officeLocation' => $officeLocation,
            'filters' => [
                'month' => (int) $request->input('month', now()->month),
                'year' => (int) $request->input('year', now()->year),
            ],
            'options' => [
                'months' => collect(range(1, 12))->map(fn ($month) => [
                    'value' => $month,
                    'label' => Carbon::create(null, $month, 1)->format('F'),
                ])->values(),
                'years' => collect(range(now()->year - 2, now()->year))->values(),
            ],
            'links' => [
                'index' => route('employee.attendance.my'),
                'todayStatus' => route('employee.attendance.today-status'),
                'checkIn' => route('employee.attendance.check-in'),
                'checkOut' => route('employee.attendance.check-out'),
                'validateLocation' => route('owner.settings.validate-location'),
            ],
        ]);
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

        if ($existing && $existing->status === 'absent') {
            return response()->json([
                'error' => 'Anda sudah melewati jam shift dan tercatat tidak hadir',
                'status' => 'absent',
            ], 400);
        }

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
        
        // Cek apakah masih dalam jam shift
        $shiftEnd = $this->getShiftEndDateTime($now, $shift);
        
        if ($now->greaterThan($shiftEnd)) {
            $attendance = $this->markAbsentForMissedShift($employee, $shift, today());
            
            return response()->json([
                'error' => 'Anda sudah melewati jam shift dan tercatat tidak hadir',
                'status' => 'absent',
                'attendance_id' => $attendance->id,
                'shift_end_time' => $shiftEnd->format('H:i'),
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
        $validator = Validator::make(
            $request->all(),
            $this->attendanceLocationRules(),
            $this->attendanceLocationMessages()
        );

        if ($validator->fails()) {
            return $this->attendanceValidationError($validator);
        }

        $integrityCheck = $this->validateAttendanceIntegrity($request, $employee);
        if ($integrityCheck) {
            return $integrityCheck;
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
            'gps_accuracy_in' => (int) round($request->accuracy),
            'location_recorded_at_in' => Carbon::parse($request->location_recorded_at),
            'device_fingerprint_in' => $request->device_fingerprint,
            'timezone_in' => $request->timezone,
            'client_time_offset_in' => $this->getClientTimeOffset($request),
            'fraud_flags' => $this->buildFraudFlags($request),
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
        $validator = Validator::make(
            $request->all(),
            $this->attendanceLocationRules(),
            $this->attendanceLocationMessages()
        );

        if ($validator->fails()) {
            return $this->attendanceValidationError($validator);
        }

        $integrityCheck = $this->validateAttendanceIntegrity($request, $employee, $attendance);
        if ($integrityCheck) {
            return $integrityCheck;
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
            'gps_accuracy_out' => (int) round($request->accuracy),
            'location_recorded_at_out' => Carbon::parse($request->location_recorded_at),
            'device_fingerprint_out' => $request->device_fingerprint,
            'timezone_out' => $request->timezone,
            'client_time_offset_out' => $this->getClientTimeOffset($request),
            'fraud_flags' => array_values(array_unique(array_merge(
                $attendance->fraud_flags ?? [],
                $this->buildFraudFlags($request)
            ))),
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
        
        // Cek apakah sudah melewati jam shift
        $isPastCheckInTime = false;
        $shiftEndTime = null;
        
        if ($shift && !$attendance) {
            $shiftEnd = $this->getShiftEndDateTime($now, $shift);
            $isPastCheckInTime = $now->greaterThan($shiftEnd);
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
                $attendance = $this->markAbsentForMissedShift($employee, $shift, today());

                return response()->json([
                    'checked_in' => false,
                    'checked_out' => false,
                    'status' => 'absent',
                    'can_check_in' => false,
                    'is_past_check_in' => true,
                    'attendance_id' => $attendance->id,
                    'shift_end_time' => $shiftEndTime,
                    'message' => 'Anda sudah melewati jam shift dan tercatat tidak hadir.',
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

    private function attendancePayload(Attendance $attendance): array
    {
        $lateMinutes = 0;

        if ($attendance->check_in_time && $attendance->shift) {
            $lateMinutes = $this->getLateMinutes(Carbon::parse($attendance->check_in_time), $attendance->shift);
        }

        $workDuration = null;
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $workDuration = Carbon::parse($attendance->check_in_time)->diffInMinutes(Carbon::parse($attendance->check_out_time));
        }

        return [
            'id' => $attendance->id,
            'date' => optional($attendance->date)->format('Y-m-d'),
            'date_label' => optional($attendance->date)->format('d/m/Y'),
            'date_long' => optional($attendance->date)->format('d F Y'),
            'employee' => $attendance->employee ? $this->employeeOption($attendance->employee) : null,
            'shift' => $attendance->shift ? $this->shiftPayload($attendance->shift) : null,
            'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : null,
            'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : null,
            'check_in_date' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('d F Y') : null,
            'check_out_date' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('d F Y') : null,
            'latitude_in' => $attendance->latitude_in,
            'longitude_in' => $attendance->longitude_in,
            'latitude_out' => $attendance->latitude_out,
            'longitude_out' => $attendance->longitude_out,
            'gps_accuracy_in' => $attendance->gps_accuracy_in,
            'gps_accuracy_out' => $attendance->gps_accuracy_out,
            'client_time_offset_in' => $attendance->client_time_offset_in,
            'client_time_offset_out' => $attendance->client_time_offset_out,
            'fraud_flags' => $attendance->fraud_flags ?? [],
            'status' => $attendance->status,
            'notes' => $attendance->notes,
            'late_minutes' => $lateMinutes,
            'late_text' => $lateMinutes > 0 ? $lateMinutes . ' menit' : null,
            'work_duration' => $workDuration,
            'work_duration_text' => $workDuration ? $this->formatDuration($workDuration) : null,
            'is_auto_checkout' => (bool) $attendance->is_auto_checkout,
            'created_at' => optional($attendance->created_at)->format('d/m/Y H:i'),
            'updated_at' => optional($attendance->updated_at)->format('d/m/Y H:i'),
            'urls' => [
                'show' => route('owner.attendance.show', $attendance),
                'update_status' => route('owner.attendance.update-status', $attendance),
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
        ];
    }

    private function shiftPayload(Shift $shift): array
    {
        return [
            'id' => $shift->id,
            'name' => $shift->name,
            'start_time' => date('H:i', strtotime($shift->start_time)),
            'end_time' => date('H:i', strtotime($shift->end_time)),
            'grace_period' => $shift->grace_period,
        ];
    }

    private function attendanceLocationRules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:250',
            'location_recorded_at' => 'required|date',
            'client_recorded_at' => 'required|date',
            'timezone' => 'required|string|max:64',
            'timezone_offset_minutes' => 'required|integer|between:-840,840',
            'device_fingerprint' => 'required|string|size:64',
            'is_mock_location' => 'nullable|boolean',
        ];
    }

    private function attendanceLocationMessages(): array
    {
        return [
            'accuracy.max' => 'Akurasi GPS masih terlalu rendah. Tunggu sampai lokasi lebih stabil lalu coba lagi.',
            'accuracy.required' => 'Akurasi GPS tidak terbaca. Aktifkan GPS lalu coba lagi.',
            'latitude.required' => 'Latitude lokasi tidak terbaca. Aktifkan izin lokasi lalu coba lagi.',
            'longitude.required' => 'Longitude lokasi tidak terbaca. Aktifkan izin lokasi lalu coba lagi.',
            'device_fingerprint.size' => 'Identitas perangkat tidak valid. Muat ulang halaman lalu coba lagi.',
        ];
    }

    private function attendanceValidationError($validator)
    {
        return response()->json([
            'error' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }

    private function validateAttendanceIntegrity(Request $request, Employee $employee, ?Attendance $attendance = null)
    {
        $locationRecordedAt = Carbon::parse($request->location_recorded_at);
        $clientRecordedAt = Carbon::parse($request->client_recorded_at);
        $now = Carbon::now();
        $clientOffsetSeconds = abs($clientRecordedAt->diffInSeconds($now, false));

        if ($request->boolean('is_mock_location')) {
            return response()->json([
                'error' => 'Absensi ditolak karena perangkat mengirim indikasi mock location.',
                'fraud_code' => 'mock_location',
            ], 400);
        }

        if ($locationRecordedAt->diffInSeconds($now, true) > 120) {
            return response()->json([
                'error' => 'Data lokasi sudah terlalu lama. Mohon aktifkan GPS lalu coba lagi.',
                'fraud_code' => 'stale_location',
            ], 400);
        }

        if ($clientOffsetSeconds > 300) {
            return response()->json([
                'error' => 'Jam perangkat tidak sesuai dengan server. Aktifkan tanggal & waktu otomatis lalu coba lagi.',
                'fraud_code' => 'device_time_mismatch',
            ], 400);
        }

        if (!in_array($request->timezone, $this->allowedAttendanceTimezones(), true)) {
            return response()->json([
                'error' => 'Zona waktu perangkat tidak sesuai area operasional absensi.',
                'fraud_code' => 'timezone_mismatch',
            ], 400);
        }

        if ($attendance && $attendance->device_fingerprint_in && $attendance->device_fingerprint_in !== $request->device_fingerprint) {
            return response()->json([
                'error' => 'Check out harus dilakukan dari perangkat yang sama dengan check in.',
                'fraud_code' => 'device_changed',
            ], 400);
        }

        $sameDeviceUsedByOtherEmployee = Attendance::whereDate('date', today())
            ->where('employee_id', '!=', $employee->id)
            ->where(function ($query) use ($request) {
                $query->where('device_fingerprint_in', $request->device_fingerprint)
                    ->orWhere('device_fingerprint_out', $request->device_fingerprint);
            })
            ->exists();

        if ($sameDeviceUsedByOtherEmployee) {
            return response()->json([
                'error' => 'Perangkat ini sudah digunakan untuk absensi akun lain hari ini.',
                'fraud_code' => 'shared_device',
            ], 400);
        }

        return null;
    }

    private function allowedAttendanceTimezones(): array
    {
        return [
            'Asia/Jakarta',
            'Asia/Bangkok',
            'Asia/Makassar',
            'Asia/Jayapura',
        ];
    }

    private function getClientTimeOffset(Request $request): int
    {
        return Carbon::parse($request->client_recorded_at)->diffInSeconds(Carbon::now(), false);
    }

    private function buildFraudFlags(Request $request): array
    {
        $flags = [];

        if ((float) $request->accuracy > 50) {
            $flags[] = 'low_gps_accuracy';
        }

        if (abs($this->getClientTimeOffset($request)) > 120) {
            $flags[] = 'client_time_offset';
        }

        return $flags;
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

    private function markAbsentForMissedShift(Employee $employee, Shift $shift, $date): Attendance
    {
        return Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => Carbon::parse($date)->toDateString(),
            ],
            [
                'shift_id' => $shift->id,
                'status' => 'absent',
                'notes' => 'Tidak hadir (melewati jam shift)',
            ]
        );
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
        $start = $date->copy()->setTimeFromTimeString($this->shiftTimeString($shift->start_time));
        $end = $date->copy()->setTimeFromTimeString($this->shiftTimeString($shift->end_time));

        if ($end->lessThanOrEqualTo($start) && $date->lessThanOrEqualTo($end)) {
            return $start->subDay();
        }

        return $start;
    }

    private function getShiftEndDateTime(Carbon $date, $shift)
    {
        $start = $date->copy()->setTimeFromTimeString($this->shiftTimeString($shift->start_time));
        $end = $date->copy()->setTimeFromTimeString($this->shiftTimeString($shift->end_time));

        if ($end->lessThanOrEqualTo($start)) {
            if ($date->lessThanOrEqualTo($end)) {
                return $end;
            }

            return $end->addDay();
        }

        return $end;
    }

    private function shiftTimeString($time): string
    {
        return $time instanceof Carbon
            ? $time->format('H:i:s')
            : Carbon::parse($time)->format('H:i:s');
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
