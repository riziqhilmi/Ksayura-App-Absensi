<?php

namespace App\Http\Controllers;

use App\Models\DailyRecap;
use App\Models\DailyRecapExpenseSession;
use App\Models\DailyRecapQrisTransaction;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QrisTransactionController extends Controller
{
    public function myIndex(Request $request)
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $date = $request->date('date')?->toDateString() ?? today()->toDateString();
        $session = $this->expenseSessionForDate($date);
        $recap = $session && !$session->closed_at ? $session->dailyRecap : null;
        $transactions = $recap ? $this->transactionsForRecap($recap) : collect();

        return Inertia::render('Employee/QrisTransactions/Index', [
            'employee' => $this->employeePayload($employee),
            'transactions' => $transactions
                ->map(fn (DailyRecapQrisTransaction $transaction) => $this->transactionPayload($transaction, 'employee.qris-transactions.evidence'))
                ->values(),
            'summary' => [
                'date' => $date,
                'total_qris_amount' => (int) $transactions->sum('amount'),
                'count' => $transactions->count(),
                'is_open' => (bool) $recap,
            ],
            'filters' => [
                'date' => $date,
                'today' => today()->toDateString(),
            ],
            'links' => [
                'index' => route('employee.qris-transactions.index'),
                'store' => route('employee.qris-transactions.store'),
                'daily_recap' => route('employee.daily-recaps.create', ['date' => $date]),
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
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'evidence' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $date = Carbon::parse($validated['transaction_date'])->toDateString();
        $session = $this->expenseSessionForDate($date);
        $recap = $session && !$session->closed_at ? $session->dailyRecap : null;

        if (!$recap) {
            return redirect()
                ->route('employee.daily-recap-expenses.index', ['date' => $date])
                ->with('error', 'Buka pengeluaran terlebih dahulu sebelum menambah QRIS.');
        }

        $transaction = $recap->qrisTransactions()->make([
            'amount' => (int) $validated['amount'],
            'sort_order' => $recap->qrisTransactions()->count(),
        ]);

        $file = $request->file('evidence');

        if ($file instanceof UploadedFile) {
            $transaction->fill($this->storeEvidence($file, $recap) + [
                'evidence_original_name' => $file->getClientOriginalName(),
                'evidence_uploaded_at' => now(),
                'evidence_expires_at' => $recap->recap_date->copy()->endOfDay(),
                'evidence_deleted_at' => null,
            ]);
        }

        $transaction->save();
        $recap->refreshQrisTotals();

        return redirect()
            ->route('employee.qris-transactions.index', ['date' => $date])
            ->with('success', 'Transaksi QRIS berhasil ditambahkan.');
    }

    public function destroy(DailyRecapQrisTransaction $transaction)
    {
        $employee = $this->currentEmployee();
        $transaction->loadMissing('dailyRecap');
        $recap = $transaction->dailyRecap;

        abort_unless($employee && $recap, 403);

        $date = $recap->recap_date->toDateString();
        $this->deleteEvidenceFile($transaction);
        $transaction->delete();
        $recap->refreshQrisTotals();

        return redirect()
            ->route('employee.qris-transactions.index', ['date' => $date])
            ->with('success', 'Transaksi QRIS berhasil dihapus.');
    }

    public function ownerIndex(Request $request)
    {
        $date = $request->date('date')?->toDateString() ?? today()->toDateString();

        $query = DailyRecapQrisTransaction::with(['dailyRecap.employee.user'])
            ->whereHas('dailyRecap', function ($query) use ($request, $date) {
                $query->whereDate('recap_date', $date);

                if ($request->filled('employee')) {
                    $query->where('employee_id', $request->employee);
                }
            });

        $statsQuery = clone $query;
        $transactions = $query->latest()->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->orderBy('employee_code')->get();

        return Inertia::render('Owner/QrisTransactions/Index', [
            'transactions' => $transactions->through(fn (DailyRecapQrisTransaction $transaction) => $this->transactionPayload($transaction, 'owner.qris-transactions.evidence')),
            'employees' => $employees->map(fn (Employee $employee) => $this->employeePayload($employee))->values(),
            'filters' => [
                'date' => $date,
                'employee' => $request->input('employee', ''),
            ],
            'stats' => [
                'count' => (clone $statsQuery)->count(),
                'total_qris_amount' => (clone $statsQuery)->sum('amount'),
            ],
            'links' => [
                'index' => route('owner.qris-transactions.index'),
            ],
        ]);
    }

    public function showEvidence(DailyRecapQrisTransaction $transaction)
    {
        $transaction->loadMissing('dailyRecap');
        $recap = $transaction->dailyRecap;

        abort_unless($recap, 404);

        $employee = $this->currentEmployee();
        $user = auth()->user();
        $isEmployeeViewingSharedQris = (bool) $employee;
        $isOwnerViewingToday = $user?->isOwner() && $recap->recap_date->isSameDay(today());

        abort_unless($isEmployeeViewingSharedQris || $isOwnerViewingToday, 403);
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

    private function expenseSessionForDate(string $date): ?DailyRecapExpenseSession
    {
        return DailyRecapExpenseSession::with(['dailyRecap.qrisTransactions'])
            ->whereDate('recap_date', $date)
            ->latest('opened_at')
            ->latest('id')
            ->first();
    }

    private function transactionsForRecap(DailyRecap $recap)
    {
        return DailyRecapQrisTransaction::with(['dailyRecap.employee.user'])
            ->where('daily_recap_id', $recap->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
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

    private function transactionPayload(DailyRecapQrisTransaction $transaction, string $evidenceRoute): array
    {
        $recap = $transaction->dailyRecap;

        return [
            'id' => $transaction->id,
            'amount' => (int) $transaction->amount,
            'employee' => $recap?->employee ? $this->employeePayload($recap->employee) : null,
            'recap_date' => optional($recap?->recap_date)->toDateString(),
            'recap_date_label' => optional($recap?->recap_date)->format('d/m/Y'),
            'has_evidence' => $transaction->hasEvidence(),
            'evidence_original_name' => $transaction->evidence_original_name,
            'evidence_uploaded_at' => optional($transaction->evidence_uploaded_at)->format('d/m/Y H:i'),
            'evidence_expires_at' => optional($transaction->evidence_expires_at)->format('d/m/Y H:i'),
            'evidence_deleted_at' => optional($transaction->evidence_deleted_at)->format('d/m/Y H:i'),
            'evidence_url' => $transaction->hasEvidence() ? route($evidenceRoute, $transaction) : null,
            'urls' => [
                'destroy' => route('employee.qris-transactions.destroy', $transaction),
            ],
            'created_at' => optional($transaction->created_at)->format('d/m/Y H:i'),
        ];
    }
}
