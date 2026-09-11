<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'inertia';

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_photo_url' => $user->profile_photo_url,
                    'avatar_initial' => $user->avatar_initial,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'navigation' => fn () => $this->navigation($request),
        ]);
    }

    private function navigation(Request $request): array
    {
        $user = $request->user();

        if (!$user) {
            return [
                'items' => [],
                'profile_url' => route('profile.edit'),
                'logout_url' => route('logout'),
            ];
        }

        if ($user->isOwner()) {
            return [
                'items' => [
                    ['label' => 'Dashboard', 'href' => route('owner.dashboard'), 'active' => $request->routeIs('owner.dashboard'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Karyawan', 'href' => route('owner.employees.index'), 'active' => $request->routeIs('owner.employees.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Penugasan Shift', 'href' => route('owner.employee-shifts.index'), 'active' => $request->routeIs('owner.employee-shifts.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Hari Libur', 'href' => route('owner.employee-holidays.calendar'), 'active' => $request->routeIs('owner.employee-holidays.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Monitoring Absensi', 'href' => route('owner.attendance.index'), 'active' => $request->routeIs('owner.attendance.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Master Shift', 'href' => route('owner.shifts.index'), 'active' => $request->routeIs('owner.shifts.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Gaji', 'href' => route('owner.salaries.index'), 'active' => $request->routeIs('owner.salaries.*'), 'inertia' => true, 'badge' => null],
                    ['label' => 'Pengajuan Cuti', 'href' => route('owner.leaves.index'), 'active' => $request->routeIs('owner.leaves.*'), 'inertia' => true, 'badge' => LeaveRequest::where('status', 'pending')->count()],
                ],
                'profile_url' => route('profile.edit'),
                'settings_url' => route('owner.settings.index'),
                'logout_url' => route('logout'),
            ];
        }

        $employee = Employee::where('user_id', $user->id)->first();
        $pendingLeaves = $employee
            ? LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count()
            : 0;

        return [
            'items' => [
                ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'active' => $request->routeIs('employee.dashboard'), 'inertia' => true, 'badge' => null],
                ['label' => 'Kalender Kerja', 'href' => route('employee.calendar.index'), 'active' => $request->routeIs('employee.calendar.*'), 'inertia' => true, 'badge' => null],
                ['label' => 'Absensi Saya', 'href' => route('employee.attendance.my'), 'active' => $request->routeIs('employee.attendance.*'), 'inertia' => true, 'badge' => null],
                ['label' => 'Gaji Saya', 'href' => route('employee.salaries.my'), 'active' => $request->routeIs('employee.salaries.*'), 'inertia' => true, 'badge' => null],
                ['label' => 'Pengajuan Cuti', 'href' => route('employee.leaves.my'), 'active' => $request->routeIs('employee.leaves.*'), 'inertia' => true, 'badge' => $pendingLeaves],
            ],
            'profile_url' => route('profile.edit'),
            'logout_url' => route('logout'),
        ];
    }
}
