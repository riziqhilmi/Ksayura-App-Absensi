<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

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

        return Inertia::render('Owner/EmployeeHolidays/Index', [
            'holidays' => $holidays->through(fn (EmployeeHoliday $holiday) => $this->holidayPayload($holiday)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'stats' => $stats,
            'filters' => [
                'employee' => $request->input('employee', ''),
                'month' => (int) $request->input('month', now()->month),
                'year' => (int) $request->input('year', now()->year),
                'status' => $request->input('status', ''),
            ],
            'options' => $this->holidayOptions(),
            'links' => [
                'index' => route('owner.employee-holidays.index'),
                'create' => route('owner.employee-holidays.create'),
                'calendar' => route('owner.employee-holidays.calendar'),
                'bulk' => route('owner.employee-holidays.bulk'),
            ],
        ]);
    }

    // Form tambah hari libur karyawan
    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return Inertia::render('Owner/EmployeeHolidays/Form', [
            'holiday' => null,
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'options' => $this->holidayOptions(),
            'links' => [
                'index' => route('owner.employee-holidays.index'),
                'store' => route('owner.employee-holidays.store'),
            ],
        ]);
    }

    // Simpan hari libur karyawan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
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
        $employeeHoliday->load('employee.user');

        return Inertia::render('Owner/EmployeeHolidays/Form', [
            'holiday' => $this->holidayPayload($employeeHoliday),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'options' => $this->holidayOptions(),
            'links' => [
                'index' => route('owner.employee-holidays.index'),
                'update' => route('owner.employee-holidays.update', $employeeHoliday),
            ],
        ]);
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
        return Inertia::render('Owner/EmployeeHolidays/Bulk', [
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'options' => $this->holidayOptions(),
            'links' => [
                'index' => route('owner.employee-holidays.index'),
                'store' => route('owner.employee-holidays.bulk.store'),
            ],
        ]);
    }

    // Simpan bulk libur karyawan
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'date' => 'required|date',
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
        
        return Inertia::render('Owner/EmployeeHolidays/Calendar', [
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'holidaysByEmployee' => collect($holidaysByEmployee)->map(fn ($items) => collect($items)->map(fn ($holiday) => $this->holidayPayload($holiday))->values())->all(),
            'month' => (int) $month,
            'year' => (int) $year,
            'daysInMonth' => $daysInMonth,
            'firstDayOfMonth' => $firstDayOfMonth,
            'monthName' => $monthName,
            'today' => $today,
            'stats' => $stats,
            'options' => $this->holidayOptions(),
            'links' => [
                'index' => route('owner.employee-holidays.index'),
                'calendar' => route('owner.employee-holidays.calendar'),
                'storeFromCalendar' => route('owner.employee-holidays.store-from-calendar'),
            ],
        ]);
    }

    // Add holiday from calendar (AJAX)
    public function storeFromCalendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
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

    private function employeePayload(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $employee->user?->name,
            'email' => $employee->user?->email,
            'employee_code' => $employee->employee_code,
            'position' => $employee->position,
            'status' => $employee->status,
        ];
    }

    private function holidayPayload(EmployeeHoliday $holiday): array
    {
        return [
            'id' => $holiday->id,
            'employee_id' => $holiday->employee_id,
            'employee' => $holiday->employee ? $this->employeePayload($holiday->employee) : null,
            'date' => optional($holiday->date)->format('Y-m-d'),
            'date_label' => optional($holiday->date)->format('d/m/Y'),
            'reason' => $holiday->reason,
            'type' => $holiday->type,
            'type_label' => $holiday->getTypeLabel(),
            'is_paid' => (bool) $holiday->is_paid,
            'status' => $holiday->status,
            'status_label' => $holiday->getStatusLabel(),
            'notes' => $holiday->notes,
            'urls' => [
                'edit' => route('owner.employee-holidays.edit', $holiday),
                'destroy' => route('owner.employee-holidays.destroy', $holiday),
            ],
        ];
    }

    private function holidayOptions(): array
    {
        return [
            'types' => [
                ['value' => 'annual', 'label' => 'Cuti Tahunan'],
                ['value' => 'sick', 'label' => 'Cuti Sakit'],
                ['value' => 'personal', 'label' => 'Cuti Pribadi'],
                ['value' => 'company', 'label' => 'Libur Perusahaan'],
                ['value' => 'other', 'label' => 'Lainnya'],
            ],
            'statuses' => [
                ['value' => 'scheduled', 'label' => 'Terjadwal'],
                ['value' => 'taken', 'label' => 'Diambil'],
                ['value' => 'cancelled', 'label' => 'Dibatalkan'],
            ],
            'months' => collect(range(1, 12))->map(fn ($month) => [
                'value' => $month,
                'label' => Carbon::create(null, $month, 1)->format('F'),
            ])->values(),
            'years' => collect(range(now()->year - 2, now()->year + 1))->values(),
        ];
    }
}
