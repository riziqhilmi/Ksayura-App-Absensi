<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyRecap;
use App\Models\DailyRecapExpenseSession;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->get();
        $today = Carbon::today();
        
        // Employee Stats
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('status', 'active')->count();
        $inactiveEmployees = $employees->where('status', 'inactive')->count();
        $resignedEmployees = $employees->where('status', 'resigned')->count();
        
        // Today's Attendance
        $todayAttendance = Attendance::whereDate('date', $today)->get();
        $todayPresent = $todayAttendance->where('status', 'present')->count();
        $todayLate = $todayAttendance->where('status', 'late')->count();
        $todayAbsent = $todayAttendance->where('status', 'absent')->count();
        $todayHalfDay = $todayAttendance->where('status', 'half_day')->count();
        $todayAutoCheckout = $todayAttendance->where('status', 'auto_checkout')->count();
        $todayTotal = $todayAttendance->count();
        
        // Attendance percentage based on active employees
        $attendancePercentage = $activeEmployees > 0 
            ? round(($todayTotal / $activeEmployees) * 100) 
            : 0;
        
        // This Month Attendance
        $monthStart = Carbon::now()->startOfMonth();
        $monthAttendance = Attendance::whereBetween('date', [$monthStart, $today])->get();
        $monthlyStats = [
            'total' => $monthAttendance->count(),
            'present' => $monthAttendance->where('status', 'present')->count(),
            'late' => $monthAttendance->where('status', 'late')->count(),
            'absent' => $monthAttendance->where('status', 'absent')->count(),
            'half_day' => $monthAttendance->where('status', 'half_day')->count(),
            'auto_checkout' => $monthAttendance->where('status', 'auto_checkout')->count(),
        ];
        
        // Daily recap and cashflow stats
        $todayRecaps = DailyRecap::with('employee.user')->whereDate('recap_date', $today)->get();
        $monthRecaps = DailyRecap::whereBetween('recap_date', [$monthStart, $today])->get();
        $openRecapSessions = DailyRecapExpenseSession::whereDate('recap_date', $today)
            ->whereNull('closed_at')
            ->count();

        $recapStats = [
            'today_recaps' => $todayRecaps->count(),
            'today_expense_amount' => (int) $todayRecaps->sum('total_expense_amount'),
            'today_qris_amount' => (int) $todayRecaps->sum('total_qris_amount'),
            'today_remaining_cash_amount' => (int) $todayRecaps->sum('remaining_cash_amount'),
            'today_capital_amount' => (int) $todayRecaps->sum('capital_amount'),
            'open_sessions' => $openRecapSessions,
            'month_recaps' => $monthRecaps->count(),
            'month_expense_amount' => (int) $monthRecaps->sum('total_expense_amount'),
            'month_qris_amount' => (int) $monthRecaps->sum('total_qris_amount'),
            'month_remaining_cash_amount' => (int) $monthRecaps->sum('remaining_cash_amount'),
            'average_qris_per_recap' => $monthRecaps->count() > 0 ? (int) round($monthRecaps->sum('total_qris_amount') / $monthRecaps->count()) : 0,
        ];
        
        // Leave Requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $approvedLeaves = LeaveRequest::where('status', 'approved')->count();
        $rejectedLeaves = LeaveRequest::where('status', 'rejected')->count();
        $totalLeaves = LeaveRequest::count();
        
        // New Employees This Month
        $newEmployees = $employees->where('created_at', '>=', $monthStart)->count();
        
        // Recent Activities (Real data)
        $recentActivities = [];
        
        // Get recent attendance
        $recentAttendances = Attendance::with(['employee.user'])
            ->latest()
            ->limit(5)
            ->get();
        
        foreach ($recentAttendances as $attendance) {
            $statusMap = [
                'present' => '✅ Hadir',
                'late' => '⚠️ Terlambat',
                'absent' => '❌ Tidak Hadir',
                'half_day' => '⏳ Setengah Hari',
                'leave' => '📋 Cuti',
                'auto_checkout' => '🤖 Auto Check Out',
            ];
            
            $recentActivities[] = [
                'user' => $attendance->employee->user->name ?? 'Unknown',
                'action' => 'Absensi: ' . ($statusMap[$attendance->status] ?? $attendance->status),
                'time' => $attendance->created_at->diffForHumans(),
                'type' => 'attendance',
                'status' => $attendance->status,
            ];
        }
        
        // Get recent leave requests
        $recentLeaves = LeaveRequest::with(['employee.user'])
            ->latest()
            ->limit(5)
            ->get();
        
        foreach ($recentLeaves as $leave) {
            $statusMap = [
                'pending' => '⏳ Menunggu',
                'approved' => '✅ Disetujui',
                'rejected' => '❌ Ditolak',
            ];
            
            $recentActivities[] = [
                'user' => $leave->employee->user->name ?? 'Unknown',
                'action' => 'Cuti Libur: ' . ($statusMap[$leave->status] ?? $leave->status),
                'time' => $leave->created_at->diffForHumans(),
                'type' => 'leave',
                'status' => $leave->status,
            ];
        }

        $recentRecaps = DailyRecap::with(['employee.user'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentRecaps as $recap) {
            $recentActivities[] = [
                'user' => $recap->employee?->user?->name ?? 'Unknown',
                'action' => 'Rekap: QRIS Rp ' . number_format((int) $recap->total_qris_amount, 0, ',', '.') . ' | Pengeluaran Rp ' . number_format((int) $recap->total_expense_amount, 0, ',', '.'),
                'time' => $recap->updated_at->diffForHumans(),
                'type' => 'recap',
                'status' => 'recap',
            ];
        }
        
        // Sort by time
        usort($recentActivities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        $recentActivities = array_slice($recentActivities, 0, 10);
        
        // Stats untuk dashboard
        $stats = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'inactive_employees' => $inactiveEmployees,
            'resigned_employees' => $resignedEmployees,
            'new_employees' => $newEmployees,
            'active_percentage' => $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100) : 0,
            'today_attendance' => $todayTotal,
            'today_present' => $todayPresent,
            'today_late' => $todayLate,
            'today_absent' => $todayAbsent,
            'today_half_day' => $todayHalfDay,
            'today_auto_checkout' => $todayAutoCheckout,
            'attendance_percentage' => $attendancePercentage,
            'pending_leaves' => $pendingLeaves,
            'approved_leaves' => $approvedLeaves,
            'rejected_leaves' => $rejectedLeaves,
            'total_leaves' => $totalLeaves,
            'monthly_attendance' => $monthlyStats,
            'recaps' => $recapStats,
        ];

        return Inertia::render('Owner/Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
        ]);
    }
}
