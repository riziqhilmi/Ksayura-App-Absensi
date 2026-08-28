<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->get();
        
        $stats = [
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'new_employees' => $employees->where('created_at', '>=', now()->startOfMonth())->count(),
            'active_percentage' => $employees->count() > 0 ? round(($employees->where('status', 'active')->count() / $employees->count()) * 100) : 0,
            'total_salary' => $employees->sum('base_salary'),
            'today_attendance' => 0,
            'attendance_percentage' => 0,
        ];

        $recent_activities = [
            [
                'user' => 'System',
                'action' => 'Dashboard Owner diakses',
                'time' => now()->format('H:i')
            ]
        ];

        return view('owner.dashboard', compact('stats', 'recent_activities'));
    }
}