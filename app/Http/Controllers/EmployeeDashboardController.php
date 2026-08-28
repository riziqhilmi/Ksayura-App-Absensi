<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        // Today's attendance status
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        // This month statistics
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $stats = [
            'total_days' => now()->diffInDays($monthStart) + 1,
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'attendance_percentage' => $attendances->count() > 0 ? 
                round(($attendances->whereIn('status', ['present', 'late'])->count() / $attendances->count()) * 100) : 0,
        ];

        // Pending leave requests
        $pendingLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        // Latest salary
        $latestSalary = Salary::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->latest()
            ->first();

        // Recent activities
        $recentActivities = [];
        
        // Get recent attendance
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentAttendances as $attendance) {
            $statusMap = [
                'present' => 'Hadir',
                'late' => 'Terlambat',
                'absent' => 'Tidak Hadir',
                'leave' => 'Cuti',
                'half_day' => 'Setengah Hari'
            ];
            
            $recentActivities[] = [
                'type' => 'attendance',
                'title' => 'Absensi ' . date('d/m/Y', strtotime($attendance->date)),
                'description' => 'Status: ' . ($statusMap[$attendance->status] ?? $attendance->status),
                'time' => $attendance->created_at->diffForHumans(),
                'icon' => $this->getStatusIcon($attendance->status),
                'color' => $this->getStatusColor($attendance->status)
            ];
        }

        // Get recent leave requests
        $recentLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentLeaves as $leave) {
            $statusMap = [
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak'
            ];
            
            $recentActivities[] = [
                'type' => 'leave',
                'title' => 'Pengajuan Cuti ' . $leave->leave_type,
                'description' => 'Status: ' . ($statusMap[$leave->status] ?? $leave->status),
                'time' => $leave->created_at->diffForHumans(),
                'icon' => 'calendar',
                'color' => $leave->status == 'pending' ? 'yellow' : ($leave->status == 'approved' ? 'green' : 'red')
            ];
        }

        // Sort by time
        usort($recentActivities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        $recentActivities = array_slice($recentActivities, 0, 10);

        return view('employee.dashboard', compact(
            'employee', 
            'todayAttendance', 
            'stats', 
            'pendingLeaves', 
            'latestSalary',
            'recentActivities'
        ));
    }

    private function getStatusIcon($status)
    {
        $icons = [
            'present' => 'check-circle',
            'late' => 'clock',
            'absent' => 'x-circle',
            'leave' => 'calendar',
            'half_day' => 'clock'
        ];
        return $icons[$status] ?? 'circle';
    }

    private function getStatusColor($status)
    {
        $colors = [
            'present' => 'green',
            'late' => 'yellow',
            'absent' => 'red',
            'leave' => 'blue',
            'half_day' => 'orange'
        ];
        return $colors[$status] ?? 'gray';
    }
}