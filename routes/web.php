<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EmployeeHolidayController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Owner Routes (Middleware: auth, verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('owner')->name('owner.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    
    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.update-status');
    Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');

    // Attendance Management
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::patch('/attendance/{attendance}/status', [AttendanceController::class, 'updateStatus'])->name('attendance.update-status');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

    // Shift Management (Master Shift)
    Route::resource('shifts', ShiftController::class);
    Route::patch('/shifts/{shift}/status', [ShiftController::class, 'updateStatus'])->name('shifts.update-status');
    Route::get('/shifts/{shift}/toggle-status', [ShiftController::class, 'toggleStatus'])->name('shifts.toggle-status');

    // Employee Shift Management (Assign Shift to Employee)
    Route::resource('employee-shifts', EmployeeShiftController::class)->except(['show']);
    Route::patch('/employee-shifts/{employeeShift}/status', [EmployeeShiftController::class, 'updateStatus'])->name('employee-shifts.update-status');

    // Employee Holiday Management
Route::get('/employee-holidays/calendar', [EmployeeHolidayController::class, 'calendar'])->name('employee-holidays.calendar');
Route::get('/employee-holidays/bulk', [EmployeeHolidayController::class, 'bulkCreate'])->name('employee-holidays.bulk');
Route::post('/employee-holidays/bulk', [EmployeeHolidayController::class, 'bulkStore'])->name('employee-holidays.bulk.store');
Route::post('/employee-holidays/store-from-calendar', [EmployeeHolidayController::class, 'storeFromCalendar'])->name('employee-holidays.store-from-calendar');
Route::get('/employee-holidays/get-by-month', [EmployeeHolidayController::class, 'getHolidaysByMonth'])->name('employee-holidays.get-by-month');
Route::resource('employee-holidays', EmployeeHolidayController::class);

    // Salary Management
    Route::get('/salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('/salaries/calculate', [SalaryController::class, 'calculate'])->name('salaries.calculate');
    Route::get('/salaries/{salary}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::get('/salaries/{salary}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
    Route::put('/salaries/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
    Route::patch('/salaries/{salary}/paid', [SalaryController::class, 'markPaid'])->name('salaries.mark-paid');
    Route::delete('/salaries/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('/salaries/export', [SalaryController::class, 'export'])->name('salaries.export');

    // Leave Requests Management
    Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/{leave}', [LeaveRequestController::class, 'show'])->name('leaves.show');
    Route::patch('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
    Route::patch('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

    // Settings
    Route::get('/settings', [CompanySettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/update-office', [CompanySettingController::class, 'updateOffice'])->name('settings.update-office');
    Route::get('/settings/office-location', [CompanySettingController::class, 'getOfficeLocation'])->name('settings.office-location');
    Route::post('/settings/validate-location', [CompanySettingController::class, 'validateLocation'])->name('settings.validate-location');
});

/*
|--------------------------------------------------------------------------
| Employee Routes (Middleware: auth, verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('employee')->name('employee.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.my');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/today-status', [AttendanceController::class, 'getTodayStatus'])->name('attendance.today-status');

    // Salary
    Route::get('/salaries', [SalaryController::class, 'mySalaries'])->name('salaries.my');
    Route::get('/salaries/{salary}', [SalaryController::class, 'myShow'])->name('salaries.show');

    Route::get('/leaves', [LeaveRequestController::class, 'myLeaves'])->name('leaves.my');
    Route::get('/leaves/create', [LeaveRequestController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}', [LeaveRequestController::class, 'myShow'])->name('leaves.show');
    Route::delete('/leaves/{leave}', [LeaveRequestController::class, 'destroy'])->name('leaves.destroy');
    Route::get('/leaves/stats', [LeaveRequestController::class, 'myStats'])->name('leaves.stats');
    Route::post('/leaves/check-availability', [LeaveRequestController::class, 'checkAvailability'])->name('leaves.check-availability');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';