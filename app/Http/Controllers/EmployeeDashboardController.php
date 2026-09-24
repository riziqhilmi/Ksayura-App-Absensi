<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyRecap;
use App\Models\DailyRecapExpenseSession;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $employee = Employee::with('user')->where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return Inertia::render('Employee/Dashboard', [
                'employee' => [
                    'user' => Auth::user(),
                    'position' => 'Staff',
                    'employee_code' => '-',
                ],
                'todayAttendance' => null,
                'stats' => [
                    'total_days' => now()->day,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'leave' => 0,
                    'attendance_percentage' => 0,
                ],
                'recapStats' => [
                    'today_recaps' => 0,
                    'today_expense_amount' => 0,
                    'today_qris_amount' => 0,
                    'today_remaining_cash_amount' => 0,
                    'month_recaps' => 0,
                    'month_expense_amount' => 0,
                    'month_qris_amount' => 0,
                    'has_open_session' => false,
                    'open_session_recap_id' => null,
                ],
                'pendingLeaves' => 0,
                'recentActivities' => [],
                'quickLinks' => [
                    'attendance' => route('employee.attendance.my'),
                    'leaveCreate' => route('employee.leaves.create'),
                    'calendar' => route('employee.calendar.index'),
                    'dailyRecap' => route('employee.daily-recaps.index'),
                    'expenses' => route('employee.daily-recap-expenses.index'),
                    'qris' => route('employee.qris-transactions.index'),
                ],
            ]);
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

        $todayRecaps = DailyRecap::whereDate('recap_date', today())->get();
        $monthRecaps = DailyRecap::whereBetween('recap_date', [$monthStart, $monthEnd])->get();
        $openSession = DailyRecapExpenseSession::with(['dailyRecap'])
            ->whereDate('recap_date', today())
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->latest('id')
            ->first();

        $recapStats = [
            'today_recaps' => $todayRecaps->count(),
            'today_expense_amount' => (int) $todayRecaps->sum('total_expense_amount'),
            'today_qris_amount' => (int) $todayRecaps->sum('total_qris_amount'),
            'today_remaining_cash_amount' => (int) $todayRecaps->sum('remaining_cash_amount'),
            'month_recaps' => $monthRecaps->count(),
            'month_expense_amount' => (int) $monthRecaps->sum('total_expense_amount'),
            'month_qris_amount' => (int) $monthRecaps->sum('total_qris_amount'),
            'has_open_session' => (bool) $openSession,
            'open_session_recap_id' => $openSession?->daily_recap_id,
        ];

        // Pending leave requests
        $pendingLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

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
                'title' => 'Pengajuan Cuti Libur',
                'description' => 'Status: ' . ($statusMap[$leave->status] ?? $leave->status),
                'time' => $leave->created_at->diffForHumans(),
                'icon' => 'calendar',
                'color' => $leave->status == 'pending' ? 'yellow' : ($leave->status == 'approved' ? 'green' : 'red')
            ];
        }

        $recentRecaps = DailyRecap::with('employee.user')
            ->latest()
            ->limit(4)
            ->get();

        foreach ($recentRecaps as $recap) {
            $recentActivities[] = [
                'type' => 'recap',
                'title' => 'Rekap Harian ' . optional($recap->recap_date)->format('d/m/Y'),
                'description' => 'QRIS Rp ' . number_format((int) $recap->total_qris_amount, 0, ',', '.') . ' | Pengeluaran Rp ' . number_format((int) $recap->total_expense_amount, 0, ',', '.'),
                'time' => $recap->updated_at->diffForHumans(),
                'icon' => 'recap',
                'color' => 'green',
            ];
        }

        // Sort by time
        usort($recentActivities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        $recentActivities = array_slice($recentActivities, 0, 10);

        return Inertia::render('Employee/Dashboard', [
            'employee' => $employee,
            'todayAttendance' => $todayAttendance,
            'stats' => $stats,
            'recapStats' => $recapStats,
            'pendingLeaves' => $pendingLeaves,
            'recentActivities' => $recentActivities,
            'quickLinks' => [
                'attendance' => route('employee.attendance.my'),
                'leaveCreate' => route('employee.leaves.create'),
                'calendar' => route('employee.calendar.index'),
                'dailyRecap' => route('employee.daily-recaps.index'),
                'expenses' => route('employee.daily-recap-expenses.index'),
                'qris' => route('employee.qris-transactions.index'),
            ],
        ]);
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
