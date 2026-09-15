<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    // ==================== OWNER METHODS ====================

    /**
     * Owner: Display a listing of all leave requests
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.user', 'approver']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->latest()->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        // Stats
        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total' => LeaveRequest::count(),
        ];

        return Inertia::render('Owner/Leaves/Index', [
            'leaveRequests' => $leaveRequests->through(fn (LeaveRequest $leave) => $this->leavePayload($leave)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeeOption($employee))->values(),
            'stats' => $stats,
            'filters' => [
                'status' => $request->input('status', ''),
                'employee' => $request->input('employee', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
            ],
            'links' => [
                'index' => route('owner.leaves.index'),
            ],
        ]);
    }

    /**
     * Owner: Display the specified leave request
     */
    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.user', 'approver']);
        return Inertia::render('Owner/Leaves/Show', [
            'leave' => $this->leavePayload($leave),
            'links' => [
                'index' => route('owner.leaves.index'),
            ],
        ]);
    }

    /**
     * Owner: Approve a leave request
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti ini sudah diproses sebelumnya.'
            ], 400);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil disetujui!'
        ]);
    }

    /**
     * Owner: Reject a leave request
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti ini sudah diproses sebelumnya.'
            ], 400);
        }

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil ditolak.'
        ]);
    }

    /**
     * Owner: Export leave requests report
     */
    public function export(Request $request)
    {
        // Implement export to Excel/PDF
        return redirect()->back()->with('info', 'Fitur export akan segera tersedia');
    }

    /**
     * Owner: Get leave statistics
     */
    public function getStats()
    {
        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total' => LeaveRequest::count(),
            'this_month' => LeaveRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Owner: Get leave requests by employee
     */
    public function getByEmployee($employeeId)
    {
        $leaves = LeaveRequest::with(['employee.user', 'approver'])
            ->where('employee_id', $employeeId)
            ->latest()
            ->get();

        return response()->json($leaves);
    }

    private function leavePayload(LeaveRequest $leave, bool $ownerUrls = true): array
    {
        $duration = $leave->start_date && $leave->end_date
            ? $leave->start_date->diffInDays($leave->end_date) + 1
            : 0;

        return [
            'id' => $leave->id,
            'employee' => $leave->employee ? $this->employeeOption($leave->employee) : null,
            'leave_type' => $leave->leave_type,
            'leave_type_label' => $this->leaveTypeLabel($leave->leave_type),
            'start_date' => optional($leave->start_date)->format('Y-m-d'),
            'start_date_label' => optional($leave->start_date)->format('d/m/Y'),
            'start_date_long' => optional($leave->start_date)->format('d F Y'),
            'end_date' => optional($leave->end_date)->format('Y-m-d'),
            'end_date_label' => optional($leave->end_date)->format('d/m/Y'),
            'end_date_long' => optional($leave->end_date)->format('d F Y'),
            'duration_days' => $duration,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'approved_by' => $leave->approver?->name,
            'approved_at' => optional($leave->approved_at)->format('d F Y H:i'),
            'rejection_reason' => $leave->rejection_reason,
            'created_at' => optional($leave->created_at)->format('d/m/Y H:i'),
            'created_at_long' => optional($leave->created_at)->format('d F Y H:i'),
            'urls' => $ownerUrls
                ? [
                    'show' => route('owner.leaves.show', $leave),
                    'approve' => route('owner.leaves.approve', $leave),
                    'reject' => route('owner.leaves.reject', $leave),
                ]
                : [
                    'show' => route('employee.leaves.show', $leave),
                    'destroy' => route('employee.leaves.destroy', $leave),
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

    private function leaveTypeLabel(?string $type): string
    {
        return 'Cuti Libur';
    }

    private function leaveOptions(): array
    {
        return [
            'types' => [
                ['value' => 'annual', 'label' => 'Cuti Libur'],
            ],
        ];
    }

    private function buildTeamLeaveCalendar(Employee $employee, Request $request): array
    {
        $targetMonth = Carbon::createFromDate(
            (int) ($request->year ?? now()->year),
            (int) ($request->month ?? now()->month),
            1
        );
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $leaves = LeaveRequest::with('employee.user')
            ->where('employee_id', '!=', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $endOfMonth->toDateString())
            ->whereDate('end_date', '>=', $startOfMonth->toDateString())
            ->get();

        $ownerHolidays = EmployeeHoliday::with('employee.user')
            ->where('employee_id', '!=', $employee->id)
            ->whereIn('status', ['scheduled', 'taken'])
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $entriesByDate = [];

        foreach ($leaves as $leave) {
            $rangeStart = Carbon::parse($leave->start_date)->max($startOfMonth);
            $rangeEnd = Carbon::parse($leave->end_date)->min($endOfMonth);

            for ($date = $rangeStart->copy(); $date->lte($rangeEnd); $date->addDay()) {
                $dateKey = $date->toDateString();
                $entriesByDate[$dateKey] ??= [];
                $entriesByDate[$dateKey][] = [
                    'employee_id' => $leave->employee_id,
                    'employee_name' => $leave->employee?->user?->name ?? 'Karyawan',
                    'status' => $leave->status,
                    'source' => 'leave_request',
                ];
            }
        }

        foreach ($ownerHolidays as $holiday) {
            $dateKey = $holiday->date->toDateString();
            $entriesByDate[$dateKey] ??= [];
            $entriesByDate[$dateKey][] = [
                'employee_id' => $holiday->employee_id,
                'employee_name' => $holiday->employee?->user?->name ?? 'Karyawan',
                'status' => 'approved',
                'source' => 'owner_holiday',
            ];
        }

        $calendarData = [];
        $stats = [
            'empty_days' => 0,
            'pending_days' => 0,
            'approved_days' => 0,
        ];

        for ($day = 1; $day <= $targetMonth->daysInMonth; $day++) {
            $date = Carbon::createFromDate($targetMonth->year, $targetMonth->month, $day);
            $dateKey = $date->toDateString();
            $entries = collect($entriesByDate[$dateKey] ?? [])
                ->groupBy('employee_id')
                ->map(function ($items) {
                    $approved = $items->firstWhere('status', 'approved');
                    return $approved ?: $items->first();
                })
                ->sortBy([
                    ['status', 'asc'],
                    ['employee_name', 'asc'],
                ])
                ->values()
                ->all();

            $status = 'empty';
            if (collect($entries)->contains('status', 'approved')) {
                $status = 'approved';
                $stats['approved_days']++;
            } elseif (count($entries) > 0) {
                $status = 'pending';
                $stats['pending_days']++;
            } else {
                $stats['empty_days']++;
            }

            $calendarData[] = [
                'date' => $dateKey,
                'day' => $day,
                'day_name' => $date->translatedFormat('D'),
                'is_today' => $dateKey === now()->toDateString(),
                'is_weekend' => $date->isWeekend(),
                'status' => $status,
                'entries' => $entries,
            ];
        }

        $previous = $targetMonth->copy()->subMonth();
        $next = $targetMonth->copy()->addMonth();

        return [
            'calendarData' => $calendarData,
            'month' => $targetMonth->month,
            'year' => $targetMonth->year,
            'monthName' => $targetMonth->translatedFormat('F Y'),
            'firstDayOfMonth' => $targetMonth->dayOfWeek,
            'stats' => $stats,
            'links' => [
                'index' => route('employee.leaves.team-calendar'),
                'previous' => route('employee.leaves.team-calendar', [
                    'month' => $previous->month,
                    'year' => $previous->year,
                ]),
                'today' => route('employee.leaves.team-calendar', [
                    'month' => now()->month,
                    'year' => now()->year,
                ]),
                'next' => route('employee.leaves.team-calendar', [
                    'month' => $next->month,
                    'year' => $next->year,
                ]),
                'create' => route('employee.leaves.create'),
                'leaves' => route('employee.leaves.my'),
            ],
        ];
    }

    private function teamLeaveConflicts(Employee $employee, string $startDate, string $endDate): array
    {
        $leaveConflicts = LeaveRequest::with('employee.user')
            ->where('employee_id', '!=', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get()
            ->map(fn (LeaveRequest $leave) => [
                'employee_name' => $leave->employee?->user?->name ?? 'Karyawan',
                'status' => $leave->status,
                'start_date' => optional($leave->start_date)->format('Y-m-d'),
                'end_date' => optional($leave->end_date)->format('Y-m-d'),
                'source' => 'leave_request',
            ])
            ->values()
            ->all();

        $holidayConflicts = EmployeeHoliday::with('employee.user')
            ->where('employee_id', '!=', $employee->id)
            ->whereIn('status', ['scheduled', 'taken'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(fn (EmployeeHoliday $holiday) => [
                'employee_name' => $holiday->employee?->user?->name ?? 'Karyawan',
                'status' => 'approved',
                'start_date' => optional($holiday->date)->format('Y-m-d'),
                'end_date' => optional($holiday->date)->format('Y-m-d'),
                'source' => 'owner_holiday',
            ])
            ->values()
            ->all();

        return collect($leaveConflicts)
            ->merge($holidayConflicts)
            ->groupBy('employee_name')
            ->map(function ($items) {
                $approved = $items->firstWhere('status', 'approved');
                return $approved ?: $items->first();
            })
            ->sortBy([
                ['status', 'asc'],
                ['employee_name', 'asc'],
            ])
            ->values()
            ->all();
    }

    // ==================== EMPLOYEE METHODS ====================

    /**
     * Employee: Display a listing of own leave requests
     */
    public function myLeaves(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $query = LeaveRequest::where('employee_id', $employee->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();
        
        $stats = [
            'pending' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'rejected')->count(),
            'total' => LeaveRequest::where('employee_id', $employee->id)->count(),
        ];

        return Inertia::render('Employee/Leaves/Index', [
            'leaves' => $leaves->through(fn (LeaveRequest $leave) => $this->leavePayload($leave, false)),
            'stats' => $stats,
            'filters' => [
                'status' => $request->input('status', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
            ],
            'links' => [
                'index' => route('employee.leaves.my'),
                'create' => route('employee.leaves.create'),
                'teamCalendar' => route('employee.leaves.team-calendar'),
            ],
        ]);
    }

    public function teamCalendar(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        return Inertia::render('Employee/Leaves/TeamCalendar', $this->buildTeamLeaveCalendar($employee, $request));
    }

    /**
     * Employee: Show the form for creating a new leave request
     */
    public function create()
    {
        // Check if employee has pending leave
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $pendingCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        return Inertia::render('Employee/Leaves/Create', [
            'pendingCount' => $pendingCount,
            'options' => $this->leaveOptions(),
            'links' => [
                'index' => route('employee.leaves.my'),
                'store' => route('employee.leaves.store'),
                'checkAvailability' => route('employee.leaves.check-availability'),
                'teamCalendar' => route('employee.leaves.team-calendar'),
            ],
            'defaults' => [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
            ],
        ]);
    }

    /**
     * Employee: Store a newly created leave request
     */
    public function store(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|string|max:50|in:annual',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if employee already has pending leave in the same period
        $existing = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Anda sudah memiliki pengajuan cuti yang masih pending pada periode ini.')
                ->withInput();
        }

        // Calculate duration
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $duration = $start->diffInDays($end) + 1;

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leaves.my')
            ->with('success', 'Pengajuan cuti berhasil dikirim! Silakan tunggu persetujuan dari Owner.');
    }

    /**
     * Employee: Display the specified leave request
     */
    public function myShow(LeaveRequest $leave)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee || $leave->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $leave->load(['employee.user', 'approver']);
        return Inertia::render('Employee/Leaves/Show', [
            'leave' => $this->leavePayload($leave, false),
            'links' => [
                'index' => route('employee.leaves.my'),
                'destroy' => route('employee.leaves.destroy', $leave),
            ],
        ]);
    }

    /**
     * Employee: Remove the specified leave request (only if pending)
     */
    public function destroy(LeaveRequest $leave)
    {
        // Check if this leave belongs to the current employee
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee || $leave->employee_id !== $employee->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus pengajuan ini.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan yang masih pending yang dapat dibatalkan');
        }

        $leave->delete();
        return redirect()->route('employee.leaves.my')
            ->with('success', 'Pengajuan cuti berhasil dibatalkan');
    }

    /**
     * Employee: Get leave balance/statistics
     */
    public function myStats()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        $year = now()->year;
        
        $totalTaken = LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->sum(\DB::raw('DATEDIFF(end_date, start_date) + 1'));

        $totalPending = LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->where('status', 'pending')
            ->count();

        $totalRejected = LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->where('status', 'rejected')
            ->count();

        // Assuming 12 days annual leave
        $annualQuota = 12;
        $remaining = $annualQuota - $totalTaken;

        return response()->json([
            'annual_quota' => $annualQuota,
            'taken' => $totalTaken,
            'remaining' => $remaining,
            'pending' => $totalPending,
            'rejected' => $totalRejected,
            'total_requests' => $totalTaken + $totalPending + $totalRejected,
        ]);
    }

    /**
     * Employee: Check if a date range is available
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        // Check if current employee already has pending or approved leave in the date range
        $conflict = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        // Check if date is in the past
        $isPast = \Carbon\Carbon::parse($request->start_date)->isPast();
        $teamConflicts = $this->teamLeaveConflicts($employee, $request->start_date, $request->end_date);

        return response()->json([
            'available' => !$conflict && !$isPast,
            'has_conflict' => $conflict,
            'is_past' => $isPast,
            'can_submit' => !$conflict && !$isPast,
            'has_team_conflict' => count($teamConflicts) > 0,
            'team_conflicts' => $teamConflicts,
        ]);
    }
}
