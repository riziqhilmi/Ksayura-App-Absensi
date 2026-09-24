<?php

namespace App\Http\Controllers;

use App\Models\DailyRecap;
use App\Models\DailyRecapExpense;
use App\Models\DailyRecapExpenseSession;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DailyRecapExpenseController extends Controller
{
    public function myIndex(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $date = $request->date('date')?->toDateString() ?? today()->toDateString();
        $session = $this->expenseSessionForDate($date);
        $expenses = $session?->expenses ?? collect();

        return Inertia::render('Employee/DailyRecapExpenses/Index', [
            'employee' => $this->employeePayload($employee),
            'expenses' => $expenses->map(fn (DailyRecapExpense $expense) => $this->expensePayload($expense))->values(),
            'summary' => [
                'session_id' => $session?->id,
                'date' => $date,
                'total_expense_amount' => (int) $expenses->sum('amount'),
                'count' => $expenses->count(),
                'is_open' => $session ? (bool) !$session->closed_at : false,
                'is_closed' => $session ? (bool) $session->closed_at : false,
                'opened_at' => optional($session?->opened_at)->format('d/m/Y H:i'),
                'closed_at' => optional($session?->closed_at)->format('d/m/Y H:i'),
                'opened_by' => $session?->openedBy ? $this->employeePayload($session->openedBy) : null,
                'closed_by' => $session?->closedBy ? $this->employeePayload($session->closedBy) : null,
            ],
            'filters' => [
                'date' => $date,
                'today' => today()->toDateString(),
            ],
            'links' => [
                'index' => route('employee.daily-recap-expenses.index'),
                'open' => route('employee.daily-recap-expenses.open'),
                'close' => route('employee.daily-recap-expenses.close'),
                'store' => route('employee.daily-recap-expenses.store'),
                'daily_recap' => route('employee.daily-recaps.create', ['date' => $date]),
            ],
        ]);
    }

    public function open(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $date = Carbon::parse($validated['expense_date'])->toDateString();
        $session = $this->expenseSessionForDate($date);

        if ($session && !$session->closed_at) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('info', 'Pengeluaran shift masih terbuka.');
        }

        $recap = DailyRecap::create([
            'employee_id' => $employee->id,
            'recap_date' => $date,
            'total_expense_amount' => 0,
            'remaining_cash_amount' => 0,
            'total_qris_amount' => 0,
            'capital_amount' => 0,
        ]);
        $session = DailyRecapExpenseSession::create([
            'daily_recap_id' => $recap->id,
            'recap_date' => $date,
            'opened_by_employee_id' => $employee->id,
            'opened_at' => now(),
        ]);
        $recap->forceFill([
            'expense_opened_at' => $session->opened_at,
            'expense_closed_at' => null,
        ])->save();

        return redirect()
            ->route('employee.daily-recap-expenses.index', ['date' => $date])
            ->with('success', 'Pengeluaran shift berhasil dibuka.');
    }

    public function close(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $date = Carbon::parse($validated['expense_date'])->toDateString();
        $session = $this->expenseSessionForDate($date);

        if (!$session) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Pengeluaran shift belum dibuka.');
        }

        if (!$session->closed_at) {
            $session->forceFill([
                'closed_by_employee_id' => $employee->id,
                'closed_at' => now(),
            ])->save();

            $session->dailyRecap->forceFill([
                'expense_closed_at' => $session->closed_at,
            ])->save();
            $session->dailyRecap->refreshExpenseTotals();
        }

        return redirect()
            ->route('employee.daily-recap-expenses.index', ['date' => $date])
            ->with('success', 'Pengeluaran shift berhasil ditutup.');
    }

    public function store(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
        ]);

        $date = Carbon::parse($validated['expense_date'])->toDateString();
        $session = $this->expenseSessionForDate($date);

        if (!$session || !$this->canEditExpenses($session)) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Buka pengeluaran shift terlebih dahulu sebelum menambah item.');
        }

        $normalizedName = $this->normalizeExpenseName($validated['name']);

        if ($this->hasDuplicateExpenseName($session, $normalizedName)) {
            return back()
                ->withErrors(['name' => 'Barang/pengeluaran tersebut sudah terinput pada rekap shift ini.'])
                ->withInput();
        }

        $recap = $session->dailyRecap;
        $recap->expenses()->create([
            'daily_recap_expense_session_id' => $session->id,
            'created_by_employee_id' => $employee->id,
            'name' => $normalizedName,
            'expense_time' => null,
            'amount' => (int) $validated['amount'],
            'sort_order' => $session->expenses()->count(),
        ]);
        $recap->refreshExpenseTotals();

        return redirect()
            ->route('employee.daily-recap-expenses.index', ['date' => $date])
            ->with('success', 'Pengeluaran barang berhasil ditambahkan.');
    }

    public function update(Request $request, DailyRecapExpense $expense)
    {
        $employee = $this->currentEmployee();
        $expense->loadMissing(['dailyRecap', 'expenseSession']);
        $recap = $expense->dailyRecap;

        abort_unless($employee && $recap, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
        ]);

        $session = $expense->expenseSession ?: $this->expenseSessionForDate($recap->recap_date->toDateString());
        $date = $recap->recap_date->toDateString();

        if (!$session || !$this->canEditExpenses($session)) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Pengeluaran shift sudah ditutup atau belum dibuka.');
        }

        $normalizedName = $this->normalizeExpenseName($validated['name']);

        if ($this->hasDuplicateExpenseName($session, $normalizedName, $expense)) {
            return back()
                ->withErrors(['name' => 'Barang/pengeluaran tersebut sudah terinput pada rekap shift ini.'])
                ->withInput();
        }

        $expense->forceFill([
            'name' => $normalizedName,
            'amount' => (int) $validated['amount'],
        ])->save();
        $recap->refreshExpenseTotals();

        return redirect()
            ->route('employee.daily-recap-expenses.index', ['date' => $date])
            ->with('success', 'Pengeluaran barang berhasil diperbarui.');
    }

    public function destroy(DailyRecapExpense $expense)
    {
        $employee = $this->currentEmployee();
        $expense->loadMissing(['dailyRecap', 'expenseSession']);
        $recap = $expense->dailyRecap;

        abort_unless($employee && $recap, 403);

        $session = $expense->expenseSession ?: $this->expenseSessionForDate($recap->recap_date->toDateString());

        $date = $recap->recap_date->toDateString();
        if (!$session || !$this->canEditExpenses($session)) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Pengeluaran shift sudah ditutup atau belum dibuka.');
        }

        $expense->delete();
        $recap->refreshExpenseTotals();

        return redirect()
            ->route('employee.daily-recap-expenses.index', ['date' => $date])
            ->with('success', 'Pengeluaran barang berhasil dihapus.');
    }

    private function currentEmployee(): ?Employee
    {
        return Employee::with('user')->where('user_id', auth()->id())->first();
    }

    private function canEditExpenses(DailyRecapExpenseSession $session): bool
    {
        return (bool) !$session->closed_at;
    }

    private function expenseSessionForDate(string $date): ?DailyRecapExpenseSession
    {
        return DailyRecapExpenseSession::with(['dailyRecap', 'openedBy.user', 'closedBy.user', 'expenses.createdBy.user'])
            ->whereDate('recap_date', $date)
            ->latest('opened_at')
            ->latest('id')
            ->first();
    }

    private function normalizeExpenseName(string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
    }

    private function normalizedExpenseNameKey(string $name): string
    {
        return mb_strtolower($this->normalizeExpenseName($name));
    }

    private function hasDuplicateExpenseName(
        DailyRecapExpenseSession $session,
        string $name,
        ?DailyRecapExpense $ignoredExpense = null
    ): bool {
        $normalizedName = $this->normalizedExpenseNameKey($name);

        return $session->expenses()
            ->when($ignoredExpense, fn ($query) => $query->whereKeyNot($ignoredExpense->getKey()))
            ->get(['id', 'name'])
            ->contains(fn (DailyRecapExpense $expense) => $this->normalizedExpenseNameKey($expense->name) === $normalizedName);
    }

    private function employeePayload(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'name' => $employee->user?->name,
            'email' => $employee->user?->email,
            'position' => $employee->position,
            'status' => $employee->status,
        ];
    }

    private function expensePayload(DailyRecapExpense $expense): array
    {
        return [
            'id' => $expense->id,
            'name' => $expense->name,
            'amount' => (int) $expense->amount,
            'created_by' => $expense->createdBy ? $this->employeePayload($expense->createdBy) : null,
            'created_by_name' => $expense->createdBy?->user?->name,
            'created_at' => optional($expense->created_at)->format('d/m/Y H:i'),
            'urls' => [
                'update' => route('employee.daily-recap-expenses.update', $expense),
                'destroy' => route('employee.daily-recap-expenses.destroy', $expense),
            ],
        ];
    }
}
