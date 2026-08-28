<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeHolidayController extends Controller
{
    // Tampilkan semua hari libur karyawan
    public function index(Request $request)
    {
        $baseQuery = EmployeeHoliday::with(['employee.user']);
        
        if ($request->filled('employee')) {
            $baseQuery->where('employee_id', $request->employee);
        }

        if ($request->filled('month')) {
            $baseQuery->whereMonth('date', $request->month);
        } else {
            $baseQuery->whereMonth('date', now()->month);
        }

        if ($request->filled('year')) {
            $baseQuery->whereYear('date', $request->year);
        } else {
            $baseQuery->whereYear('date', now()->year);
        }

        $query = clone $baseQuery;
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $holidays = $query->orderBy('date')->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->get();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'scheduled' => (clone $baseQuery)->where('status', 'scheduled')->count(),
            'taken' => (clone $baseQuery)->where('status', 'taken')->count(),
            'upcoming' => (clone $baseQuery)->where('date', '>=', today())->where('status', 'scheduled')->count(),
        ];

        return view('owner.employee-holidays.index', compact('holidays', 'employees', 'stats'));
    }

    // Form tambah hari libur karyawan
    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('owner.employee-holidays.create', compact('employees'));
    }

    // Simpan hari libur karyawan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
            'type' => 'required|in:annual,sick,personal,company,other',
            'is_paid' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek apakah karyawan sudah punya libur di tanggal ini
        $existing = EmployeeHoliday::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->whereIn('status', ['scheduled', 'taken'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Karyawan sudah memiliki hari libur pada tanggal ' . date('d/m/Y', strtotime($request->date)))
                ->withInput();
        }

        EmployeeHoliday::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'reason' => $request->reason,
            'type' => $request->type,
            'is_paid' => $request->is_paid ?? true,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.employee-holidays.index')
            ->with('success', 'Hari libur karyawan berhasil ditambahkan!');
    }

    // Edit hari libur karyawan
    public function edit(EmployeeHoliday $employeeHoliday)
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('owner.employee-holidays.edit', compact('employeeHoliday', 'employees'));
    }

    // Update hari libur karyawan
    public function update(Request $request, EmployeeHoliday $employeeHoliday)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'type' => 'required|in:annual,sick,personal,company,other',
            'is_paid' => 'boolean',
            'status' => 'required|in:scheduled,taken,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employeeHoliday->update([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'reason' => $request->reason,
            'type' => $request->type,
            'is_paid' => $request->is_paid ?? true,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.employee-holidays.index')
            ->with('success', 'Hari libur karyawan berhasil diperbarui!');
    }

    // Hapus hari libur karyawan
    public function destroy(EmployeeHoliday $employeeHoliday)
    {
        $employeeHoliday->delete();
        return redirect()->route('owner.employee-holidays.index')
            ->with('success', 'Hari libur karyawan berhasil dihapus!');
    }

    // Bulk tambah libur untuk banyak karyawan
    public function bulkCreate()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('owner.employee-holidays.bulk', compact('employees'));
    }

    // Simpan bulk libur karyawan
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
            'type' => 'required|in:annual,sick,personal,company,other',
            'is_paid' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $successCount = 0;
        $errorMessages = [];

        foreach ($request->employee_ids as $employeeId) {
            $existing = EmployeeHoliday::where('employee_id', $employeeId)
                ->where('date', $request->date)
                ->whereIn('status', ['scheduled', 'taken'])
                ->first();

            if ($existing) {
                $employee = Employee::find($employeeId);
                $errorMessages[] = $employee->user->name . ' sudah punya libur di tanggal ini.';
                continue;
            }

            EmployeeHoliday::create([
                'employee_id' => $employeeId,
                'date' => $request->date,
                'reason' => $request->reason,
                'type' => $request->type,
                'is_paid' => $request->is_paid ?? true,
                'status' => 'scheduled',
                'notes' => $request->notes,
            ]);

            $successCount++;
        }

        $message = $successCount . ' karyawan berhasil ditambahkan hari libur.';
        if (!empty($errorMessages)) {
            $message .= ' Gagal: ' . implode(' ', $errorMessages);
        }

        return redirect()->route('owner.employee-holidays.index')
            ->with('success', $message);
    }
    public function calendar(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        $employees = Employee::with('user')->where('status', 'active')->get();
        
        // Get holidays for this month
        $holidays = EmployeeHoliday::with(['employee.user'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['scheduled', 'taken'])
            ->get();
        
        // Group holidays by employee
        $holidaysByEmployee = [];
        foreach ($holidays as $holiday) {
            $employeeId = $holiday->employee_id;
            if (!isset($holidaysByEmployee[$employeeId])) {
                $holidaysByEmployee[$employeeId] = [];
            }
            $holidaysByEmployee[$employeeId][] = $holiday;
        }
        
        // Get days in month
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->dayOfWeek;
        
        // Month name
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        
        // Get today
        $today = Carbon::now()->format('Y-m-d');
        
        // Stats
        $stats = [
            'total' => $holidays->count(),
            'scheduled' => $holidays->where('status', 'scheduled')->count(),
            'taken' => $holidays->where('status', 'taken')->count(),
            'upcoming' => $holidays->where('date', '>=', today())->where('status', 'scheduled')->count(),
        ];
        
        return view('owner.employee-holidays.calendar', compact(
            'employees', 
            'holidaysByEmployee', 
            'month', 
            'year', 
            'daysInMonth', 
            'firstDayOfMonth',
            'monthName',
            'today',
            'stats'
        ));
    }

    // Add holiday from calendar (AJAX)
    public function storeFromCalendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date|after_or_equal:today',
            'type' => 'required|in:annual,sick,personal,company,other',
            'reason' => 'nullable|string|max:255',
            'is_paid' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        // Check if already exists
        $existing = EmployeeHoliday::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->whereIn('status', ['scheduled', 'taken'])
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan sudah memiliki hari libur pada tanggal ini.'
            ], 400);
        }
        
        $holiday = EmployeeHoliday::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'reason' => $request->reason,
            'type' => $request->type,
            'is_paid' => $request->is_paid ?? true,
            'status' => 'scheduled',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil ditambahkan!',
            'holiday' => [
                'id' => $holiday->id,
                'date' => $holiday->date->format('Y-m-d'),
                'type_label' => $holiday->getTypeLabel(),
                'status_label' => $holiday->getStatusLabel(),
                'employee_name' => $holiday->employee->user->name ?? '-',
            ]
        ]);
    }

    // Get holidays for specific month (AJAX)
    public function getHolidaysByMonth(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        $holidays = EmployeeHoliday::with(['employee.user'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['scheduled', 'taken'])
            ->get()
            ->map(function($holiday) {
                return [
                    'id' => $holiday->id,
                    'employee_id' => $holiday->employee_id,
                    'employee_name' => $holiday->employee->user->name ?? '-',
                    'date' => $holiday->date->format('Y-m-d'),
                    'date_formatted' => $holiday->date->format('d/m/Y'),
                    'reason' => $holiday->reason,
                    'type' => $holiday->type,
                    'type_label' => $holiday->getTypeLabel(),
                    'status' => $holiday->status,
                    'status_label' => $holiday->getStatusLabel(),
                    'is_paid' => $holiday->is_paid,
                ];
            });
        
        return response()->json($holidays);
    }
}
