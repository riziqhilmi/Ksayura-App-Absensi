<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $leaveRequests = $query->latest()->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        // Stats
        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total' => LeaveRequest::count(),
        ];

        return view('owner.leaves.index', compact('leaveRequests', 'employees', 'stats'));
    }

    /**
     * Owner: Display the specified leave request
     */
    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.user', 'approver']);
        return view('owner.leaves.show', compact('leave'));
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

        $leaves = $query->latest()->paginate(10);
        
        $stats = [
            'pending' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('employee_id', $employee->id)->where('status', 'rejected')->count(),
            'total' => LeaveRequest::where('employee_id', $employee->id)->count(),
        ];

        return view('employee.leaves.index', compact('leaves', 'stats'));
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

        return view('employee.leaves.create', compact('pendingCount'));
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
            'leave_type' => 'required|string|max:50|in:annual,sick,personal,maternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
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
        return view('employee.leaves.show', compact('leave'));
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

        // Check if there's any pending or approved leave in the date range
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

        return response()->json([
            'available' => !$conflict && !$isPast,
            'has_conflict' => $conflict,
            'is_past' => $isPast,
        ]);
    }
}