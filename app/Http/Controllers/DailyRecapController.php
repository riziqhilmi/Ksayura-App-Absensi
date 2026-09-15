<?php

namespace App\Http\Controllers;

use App\Models\DailyRecap;
use App\Models\DailyRecapExpense;
use App\Models\DailyRecapExpenseSession;
use App\Models\DailyRecapQrisTransaction;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DailyRecapController extends Controller
{
    public function myIndex(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $query = DailyRecap::with([
                'employee.user',
                'expenseSessions.openedBy.user',
                'expenseSessions.closedBy.user',
            ])
            ->withCount(['expenses', 'qrisTransactions']);

        if ($request->filled('date')) {
            $query->whereDate('recap_date', $request->date);
        }

        $recaps = $query->latest('recap_date')->paginate(15)->withQueryString();

        return Inertia::render('Employee/DailyRecaps/List', [
            'recaps' => $recaps->through(fn (DailyRecap $recap) => $this->recapListPayload($recap, false)),
            'filters' => [
                'date' => $request->input('date', ''),
                'today' => today()->toDateString(),
            ],
            'links' => [
                'index' => route('employee.daily-recaps.index'),
                'create' => route('employee.daily-recaps.create'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $date = $request->date('date')?->toDateString() ?? today()->toDateString();
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $expenseSession = $this->latestExpenseSessionForDate($date);

        if (!$expenseSession || $expenseSession->closed_at) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Buka pengeluaran terlebih dahulu sebelum membuat rekap baru.');
        }

        $expenseSession->loadMissing(['expenses', 'openedBy.user', 'closedBy.user']);

        $recap = $expenseSession->dailyRecap?->load(['employee.user', 'qrisTransactions']);

        return Inertia::render('Employee/DailyRecaps/Index', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->user?->name,
                'employee_code' => $employee->employee_code,
                'position' => $employee->position,
            ],
            'recap' => $this->recapPayloadForEmployeeDate($recap, $employee, $date, 'employee.qris-transactions.evidence', $expenseSession),
            'expenseSession' => $this->expenseSessionPayload($expenseSession),
            'filters' => [
                'date' => $date,
                'today' => today()->toDateString(),
            ],
            'links' => [
                'index' => route('employee.daily-recaps.index'),
                'form' => route('employee.daily-recaps.create'),
                'store' => route('employee.daily-recaps.store'),
                'expenses' => route('employee.daily-recap-expenses.index', ['date' => $date]),
                'qris' => route('employee.qris-transactions.index', ['date' => $date]),
            ],
        ]);
    }

    public function myShow(DailyRecap $dailyRecap)
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 403);

        $dailyRecap->load(['employee.user', 'expenseSessions.openedBy.user', 'expenseSessions.closedBy.user', 'qrisTransactions']);

        return Inertia::render('Employee/DailyRecaps/Show', [
            'recap' => $this->recapPayload($dailyRecap, 'employee.qris-transactions.evidence', true),
            'links' => [
                'index' => route('employee.daily-recaps.index'),
                'edit' => $this->canEditRecap($dailyRecap)
                    ? route('employee.daily-recaps.edit', $dailyRecap)
                    : null,
            ],
        ]);
    }

    public function edit(DailyRecap $dailyRecap)
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 403);

        if (!$this->canEditRecap($dailyRecap)) {
            return redirect()
                ->route('employee.daily-recaps.show', $dailyRecap)
                ->with('error', 'Rekap ini sudah ditutup. Buka pengeluaran baru untuk membuat rekap berikutnya.');
        }

        return redirect()->route('employee.daily-recaps.create', [
            'date' => $dailyRecap->recap_date->toDateString(),
        ]);
    }

    public function ownerIndex(Request $request)
    {
        $query = DailyRecap::with(['employee.user'])->withCount(['expenses', 'qrisTransactions']);

        if ($request->filled('date')) {
            $query->whereDate('recap_date', $request->date);
        } else {
            $query->whereDate('recap_date', today());
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        $recaps = $query->latest('recap_date')->latest()->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->orderBy('employee_code')->get();

        return Inertia::render('Owner/DailyRecaps/Index', [
            'recaps' => $recaps->through(fn (DailyRecap $recap) => $this->recapListPayload($recap, true)),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'filters' => [
                'date' => $request->input('date', today()->toDateString()),
                'employee' => $request->input('employee', ''),
            ],
            'stats' => [
                'total_recaps' => (clone $query)->count(),
                'total_expense_amount' => (clone $query)->sum('total_expense_amount'),
                'total_qris_amount' => (clone $query)->sum('total_qris_amount'),
                'total_capital_amount' => (clone $query)->sum('capital_amount'),
            ],
            'links' => [
                'index' => route('owner.daily-recaps.index'),
            ],
        ]);
    }

    public function ownerShow(DailyRecap $dailyRecap)
    {
        $dailyRecap->load(['employee.user', 'qrisTransactions']);

        return Inertia::render('Owner/DailyRecaps/Show', [
            'recap' => $this->recapPayload($dailyRecap, 'owner.qris-transactions.evidence', true),
            'links' => [
                'index' => route('owner.daily-recaps.index', [
                    'date' => $dailyRecap->recap_date->toDateString(),
                ]),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $validated = $request->validate([
            'recap_date' => ['required', 'date', 'before_or_equal:today'],
            'capital_amount' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'remaining_cash_amount' => ['required', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $recap = DB::transaction(function () use ($validated, $employee) {
            $recapDate = Carbon::parse($validated['recap_date'])->toDateString();
            $expenseSession = $this->latestExpenseSessionForDate($recapDate);

            if (!$expenseSession || $expenseSession->closed_at) {
                return null;
            }

            $recap = $expenseSession->dailyRecap;

            if (!$recap) {
                return null;
            }

            $totalQris = (int) $recap->qrisTransactions()->sum('amount');
            $totalExpenses = (int) $expenseSession->expenses()->sum('amount');

            $recap->fill([
                'total_expense_amount' => $totalExpenses,
                'capital_amount' => (int) $validated['capital_amount'],
                'remaining_cash_amount' => (int) $validated['remaining_cash_amount'],
                'total_qris_amount' => $totalQris,
            ]);
            $recap->save();

            $recap->refreshQrisTotals();

            return $recap->fresh(['qrisTransactions']);
        });

        if (!$recap) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $validated['recap_date']])
                ->with('error', 'Buka pengeluaran terlebih dahulu sebelum membuat rekap baru.');
        }

        return redirect()
            ->route('employee.daily-recaps.show', $recap)
            ->with('success', 'Rekap harian berhasil disimpan.');
    }

    public function showQrisEvidence(DailyRecapQrisTransaction $transaction)
    {
        $employee = $this->currentEmployee();
        $transaction->loadMissing('dailyRecap.employee');
        $recap = $transaction->dailyRecap;

        abort_unless($recap, 404);

        $user = auth()->user();
        $isEmployee = (bool) $employee;
        $isOwnerViewingToday = $user?->isOwner() && $recap->recap_date->isSameDay(today());

        abort_unless($isEmployee || $isOwnerViewingToday, 403);

        abort_unless($transaction->hasEvidence(), 404);
        abort_unless(Storage::disk('local')->exists($transaction->evidence_path), 404);

        return Storage::disk('local')->response(
            $transaction->evidence_path,
            $transaction->evidence_original_name
        );
    }

    private function currentEmployee(): ?Employee
    {
        return Employee::with('user')->where('user_id', auth()->id())->first();
    }

    private function syncExpenses(DailyRecap $recap, array $expenses): void
    {
        $keptIds = [];

        foreach ($expenses as $index => $expense) {
            $row = $this->existingChildRow($recap, 'expenses', $expense['id'] ?? null)
                ?? $recap->expenses()->make();

            $row->fill([
                'name' => $expense['name'],
                'expense_time' => $expense['expense_time'] ?? null,
                'amount' => (int) $expense['amount'],
                'sort_order' => $index,
            ]);
            $row->save();
            $keptIds[] = $row->id;
        }

        $recap->expenses()
            ->when($keptIds, fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
    }

    private function syncQrisTransactions(Request $request, DailyRecap $recap, array $transactions): void
    {
        $keptIds = [];

        foreach ($transactions as $index => $transaction) {
            $row = $this->existingChildRow($recap, 'qrisTransactions', $transaction['id'] ?? null)
                ?? $recap->qrisTransactions()->make();

            $row->fill([
                'amount' => (int) $transaction['amount'],
                'sort_order' => $index,
            ]);

            $file = $request->file("qris_transactions.$index.evidence");

            if ($file instanceof UploadedFile) {
                $this->deleteEvidenceFile($row);
                $stored = $this->storeEvidence($file, $recap);

                $row->fill($stored + [
                    'evidence_original_name' => $file->getClientOriginalName(),
                    'evidence_uploaded_at' => now(),
                    'evidence_expires_at' => $recap->recap_date->copy()->endOfDay(),
                    'evidence_deleted_at' => null,
                ]);
            }

            $row->save();
            $keptIds[] = $row->id;
        }

        $removedRows = $recap->qrisTransactions()
            ->when($keptIds, fn ($query) => $query->whereNotIn('id', $keptIds))
            ->get();

        foreach ($removedRows as $removedRow) {
            $this->deleteEvidenceFile($removedRow);
            $removedRow->delete();
        }
    }

    private function existingChildRow(DailyRecap $recap, string $relation, $id)
    {
        if (!$id) {
            return null;
        }

        return $recap->{$relation}()->whereKey($id)->first();
    }

    private function storeEvidence(UploadedFile $file, DailyRecap $recap): array
    {
        $directory = 'daily-recap-qris/'.$recap->recap_date->format('Y/m');
        $filename = $recap->employee_id.'-'.$recap->recap_date->format('Ymd').'-'.Str::uuid().'.jpg';
        $path = $directory.'/'.$filename;

        $optimized = $this->optimizedJpegContents($file);

        if ($optimized !== null) {
            Storage::disk('local')->put($path, $optimized);

            return [
                'evidence_path' => $path,
                'evidence_mime_type' => 'image/jpeg',
                'evidence_size' => strlen($optimized),
            ];
        }

        $storedPath = $file->store($directory, 'local');

        return [
            'evidence_path' => $storedPath,
            'evidence_mime_type' => $file->getMimeType(),
            'evidence_size' => $file->getSize(),
        ];
    }

    private function optimizedJpegContents(UploadedFile $file): ?string
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (!$source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxSide = 1600;
        $scale = min(1, $maxSide / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($target, null, 78);
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        return $contents === false ? null : $contents;
    }

    private function deleteEvidenceFile(DailyRecapQrisTransaction $transaction): void
    {
        if ($transaction->evidence_path) {
            Storage::disk('local')->delete($transaction->evidence_path);
        }
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

    private function recapListPayload(DailyRecap $recap, bool $ownerUrls): array
    {
        $expenses = $this->expensesForRecap($recap);
        $qrisTransactions = $this->qrisTransactionsForRecap($recap);
        $latestSession = $this->latestSessionForRecap($recap);

        return [
            'id' => $recap->id,
            'recap_date' => $recap->recap_date->toDateString(),
            'recap_date_label' => $recap->recap_date->format('d/m/Y'),
            'recap_date_long' => $recap->recap_date->format('d F Y'),
            'employee' => $recap->employee ? $this->employeePayload($recap->employee) : null,
            'recapped_by' => $recap->employee ? $this->employeePayload($recap->employee) : null,
            'opened_by' => $latestSession?->openedBy ? $this->employeePayload($latestSession->openedBy) : null,
            'closed_by' => $latestSession?->closedBy ? $this->employeePayload($latestSession->closedBy) : null,
            'opened_at' => optional($latestSession?->opened_at)->format('d/m/Y H:i'),
            'closed_at' => optional($latestSession?->closed_at)->format('d/m/Y H:i'),
            'is_open' => $latestSession ? (bool) !$latestSession->closed_at : false,
            'total_expense_amount' => (int) $expenses->sum('amount'),
            'remaining_cash_amount' => (int) $recap->remaining_cash_amount,
            'total_qris_amount' => (int) $qrisTransactions->sum('amount'),
            'capital_amount' => (int) $recap->capital_amount,
            'expenses_count' => $expenses->count(),
            'qris_transactions_count' => $qrisTransactions->count(),
            'updated_at' => optional($recap->updated_at)->format('d/m/Y H:i'),
            'urls' => [
                'show' => $ownerUrls
                    ? route('owner.daily-recaps.show', $recap)
                    : route('employee.daily-recaps.show', $recap),
                'edit' => $ownerUrls || !$this->canEditRecap($recap)
                    ? null
                    : route('employee.daily-recaps.edit', $recap),
            ],
        ];
    }

    private function recapPayloadForEmployeeDate(
        ?DailyRecap $recap,
        Employee $employee,
        string $date,
        string $evidenceRoute,
        ?DailyRecapExpenseSession $expenseSession = null
    ): array
    {
        $sharedExpenses = $expenseSession?->expenses ?? $this->sharedExpensesForDate($date);
        $sharedQris = $recap ? $this->qrisTransactionsForRecap($recap) : collect();

        if ($recap) {
            return [
                'id' => $recap->id,
                'recap_date' => $recap->recap_date->toDateString(),
                'recap_date_label' => $recap->recap_date->format('d F Y'),
                'employee' => $recap->employee ? $this->employeePayload($recap->employee) : $this->employeePayload($employee),
                'total_expense_amount' => (int) $sharedExpenses->sum('amount'),
                'remaining_cash_amount' => (int) $recap->remaining_cash_amount,
                'total_qris_amount' => (int) $sharedQris->sum('amount'),
                'capital_amount' => (int) $recap->capital_amount,
                'expenses' => $this->sharedExpensePayload($sharedExpenses),
                'qris_transactions' => $this->sharedQrisPayload($sharedQris, $evidenceRoute),
                'updated_at' => optional($recap->updated_at)->format('d/m/Y H:i'),
            ];
        }

        return [
            'id' => null,
            'recap_date' => $date,
            'recap_date_label' => Carbon::parse($date)->format('d F Y'),
            'employee' => $this->employeePayload($employee),
            'total_expense_amount' => (int) $sharedExpenses->sum('amount'),
            'remaining_cash_amount' => 0,
            'total_qris_amount' => (int) $sharedQris->sum('amount'),
            'capital_amount' => 0,
            'expenses' => $this->sharedExpensePayload($sharedExpenses),
            'qris_transactions' => $this->sharedQrisPayload($sharedQris, $evidenceRoute),
            'updated_at' => null,
        ];
    }

    private function recapPayload(DailyRecap $recap, string $evidenceRoute, bool $useSharedExpenses = false): array
    {
        $expenses = $useSharedExpenses ? $this->expensesForRecap($recap) : $recap->expenses;
        $qrisTransactions = $useSharedExpenses ? $this->qrisTransactionsForRecap($recap) : $recap->qrisTransactions;

        return [
            'id' => $recap->id,
            'recap_date' => $recap->recap_date->toDateString(),
            'recap_date_label' => $recap->recap_date->format('d F Y'),
            'employee' => $recap->employee ? $this->employeePayload($recap->employee) : null,
            'session' => $this->expenseSessionPayload($this->latestSessionForRecap($recap)),
            'total_expense_amount' => $useSharedExpenses
                ? (int) $expenses->sum('amount')
                : (int) $recap->total_expense_amount,
            'remaining_cash_amount' => (int) $recap->remaining_cash_amount,
            'total_qris_amount' => $useSharedExpenses
                ? (int) $qrisTransactions->sum('amount')
                : (int) $recap->total_qris_amount,
            'capital_amount' => (int) $recap->capital_amount,
            'expenses' => $this->sharedExpensePayload($expenses),
            'qris_transactions' => $this->sharedQrisPayload($qrisTransactions, $evidenceRoute),
            'updated_at' => optional($recap->updated_at)->format('d/m/Y H:i'),
        ];
    }

    private function expensesForRecap(DailyRecap $recap)
    {
        return DailyRecapExpense::with(['expenseSession.openedBy.user', 'dailyRecap'])
            ->where(function ($query) use ($recap) {
                $query->whereHas('expenseSession', function ($sessionQuery) use ($recap) {
                    $sessionQuery->where('daily_recap_id', $recap->id);
                })->orWhere(function ($legacyQuery) use ($recap) {
                    $legacyQuery->whereNull('daily_recap_expense_session_id')
                        ->where('daily_recap_id', $recap->id);
                });
            })
            ->orderBy('daily_recap_expense_session_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function sharedExpensesForDate(string $date)
    {
        return DailyRecapExpense::with(['expenseSession.openedBy.user', 'dailyRecap'])
            ->where(function ($query) use ($date) {
                $query->whereHas('expenseSession', function ($sessionQuery) use ($date) {
                    $sessionQuery->whereDate('recap_date', $date);
                })->orWhere(function ($legacyQuery) use ($date) {
                    $legacyQuery->whereNull('daily_recap_expense_session_id')
                        ->whereHas('dailyRecap', function ($recapQuery) use ($date) {
                            $recapQuery->whereDate('recap_date', $date);
                        });
                });
            })
            ->orderBy('daily_recap_expense_session_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function sharedExpensePayload($expenses): array
    {
        return $expenses->map(fn (DailyRecapExpense $expense) => [
            'id' => $expense->id,
            'name' => $expense->name,
            'amount' => (int) $expense->amount,
            'session_id' => $expense->daily_recap_expense_session_id,
            'session_opened_by' => $expense->expenseSession?->openedBy?->user?->name,
        ])->values()->all();
    }

    private function sharedQrisTransactionsForDate(string $date)
    {
        return DailyRecapQrisTransaction::with(['dailyRecap.employee.user'])
            ->whereHas('dailyRecap', function ($query) use ($date) {
                $query->whereDate('recap_date', $date);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    private function qrisTransactionsForRecap(DailyRecap $recap)
    {
        return DailyRecapQrisTransaction::with(['dailyRecap.employee.user'])
            ->where('daily_recap_id', $recap->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    private function sharedQrisPayload($transactions, string $evidenceRoute): array
    {
        return $transactions->map(fn (DailyRecapQrisTransaction $transaction) => [
            'id' => $transaction->id,
            'amount' => (int) $transaction->amount,
            'employee' => $transaction->dailyRecap?->employee ? $this->employeePayload($transaction->dailyRecap->employee) : null,
            'has_evidence' => $transaction->hasEvidence(),
            'evidence_original_name' => $transaction->evidence_original_name,
            'evidence_uploaded_at' => optional($transaction->evidence_uploaded_at)->format('d/m/Y H:i'),
            'evidence_expires_at' => optional($transaction->evidence_expires_at)->format('d/m/Y H:i'),
            'evidence_deleted_at' => optional($transaction->evidence_deleted_at)->format('d/m/Y H:i'),
            'evidence_url' => $transaction->hasEvidence()
                ? route($evidenceRoute, $transaction)
                : null,
        ])->values()->all();
    }

    private function latestExpenseSessionForDate(string $date): ?DailyRecapExpenseSession
    {
        return DailyRecapExpenseSession::with(['dailyRecap', 'openedBy.user', 'closedBy.user'])
            ->whereDate('recap_date', $date)
            ->latest('opened_at')
            ->latest('id')
            ->first();
    }

    private function latestSessionForRecap(DailyRecap $recap): ?DailyRecapExpenseSession
    {
        if ($recap->relationLoaded('expenseSessions')) {
            return $recap->expenseSessions
                ->sortByDesc(fn (DailyRecapExpenseSession $session) => $session->opened_at?->timestamp ?? 0)
                ->sortByDesc('id')
                ->first();
        }

        return $recap->expenseSessions()
            ->with(['openedBy.user', 'closedBy.user'])
            ->latest('opened_at')
            ->latest('id')
            ->first();
    }

    private function canEditRecap(DailyRecap $recap): bool
    {
        return $recap->expenseSessions()
            ->whereNull('closed_at')
            ->exists();
    }

    private function expenseSessionPayloadForDate(string $date): array
    {
        return $this->expenseSessionPayload($this->latestExpenseSessionForDate($date));
    }

    private function expenseSessionPayload(?DailyRecapExpenseSession $session): array
    {
        return [
            'id' => $session?->id,
            'is_open' => $session ? (bool) !$session->closed_at : false,
            'is_closed' => $session ? (bool) $session->closed_at : false,
            'opened_at' => optional($session?->opened_at)->format('d/m/Y H:i'),
            'closed_at' => optional($session?->closed_at)->format('d/m/Y H:i'),
            'opened_by' => $session?->openedBy ? $this->employeePayload($session->openedBy) : null,
            'closed_by' => $session?->closedBy ? $this->employeePayload($session->closedBy) : null,
        ];
    }
}
