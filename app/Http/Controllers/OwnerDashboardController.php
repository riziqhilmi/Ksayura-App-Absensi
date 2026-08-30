<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        
        // Salary Stats
        $totalSalary = $employees->sum('daily_rate') * 30; // Estimasi gaji bulanan (30 hari)
        $averageSalary = $employees->count() > 0 
            ? round($employees->avg('daily_rate')) 
            : 0;
        
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
                'action' => 'Cuti ' . $leave->leave_type . ': ' . ($statusMap[$leave->status] ?? $leave->status),
                'time' => $leave->created_at->diffForHumans(),
                'type' => 'leave',
                'status' => $leave->status,
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
            'total_salary' => $totalSalary,
            'average_salary' => $averageSalary,
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
        ];

        return view('owner.dashboard', compact('stats', 'recentActivities'));
    }
}