<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('created_at', 'desc')->get();
        $activeShifts = Shift::where('status', 'active')->count();
        $inactiveShifts = Shift::where('status', 'inactive')->count();
        
        return Inertia::render('Owner/Shifts/Index', [
            'shifts' => $shifts->map(fn (Shift $shift) => $this->shiftPayload($shift))->values(),
            'stats' => [
                'total' => $shifts->count(),
                'active' => $activeShifts,
                'inactive' => $inactiveShifts,
            ],
            'links' => [
                'create' => route('owner.shifts.create'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Owner/Shifts/Form', [
            'mode' => 'create',
            'shift' => null,
            'links' => [
                'index' => route('owner.shifts.index'),
                'submit' => route('owner.shifts.store'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->shiftValidator($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Shift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'break_start' => $request->break_start,
            'break_end' => $request->break_end,
            'grace_period' => $request->grace_period,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.shifts.index')
            ->with('success', 'Shift berhasil ditambahkan!');
    }

    public function show(Shift $shift)
    {
        return Inertia::render('Owner/Shifts/Show', [
            'shift' => $this->shiftPayload($shift),
            'links' => [
                'index' => route('owner.shifts.index'),
                'edit' => route('owner.shifts.edit', $shift),
            ],
        ]);
    }

    public function edit(Shift $shift)
    {
        return Inertia::render('Owner/Shifts/Form', [
            'mode' => 'edit',
            'shift' => $this->shiftPayload($shift),
            'links' => [
                'index' => route('owner.shifts.index'),
                'submit' => route('owner.shifts.update', $shift),
            ],
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validator = $this->shiftValidator($request, true);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $shift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'break_start' => $request->break_start,
            'break_end' => $request->break_end,
            'grace_period' => $request->grace_period,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('owner.shifts.index')
            ->with('success', 'Shift berhasil diperbarui!');
    }

    public function destroy(Shift $shift)
    {
        // Check if shift has attendances
        if ($shift->attendances()->count() > 0) {
            return redirect()->route('owner.shifts.index')
                ->with('error', 'Shift tidak dapat dihapus karena sudah digunakan dalam absensi.');
        }

        $shift->delete();
        return redirect()->route('owner.shifts.index')
            ->with('success', 'Shift berhasil dihapus!');
    }

    public function updateStatus(Request $request, Shift $shift)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $shift->update(['status' => $request->status]);

        return response()->json([
            'success' => true, 
            'message' => 'Status shift berhasil diperbarui',
            'status' => $request->status
        ]);
    }

    public function toggleStatus(Shift $shift)
    {
        $newStatus = $shift->status === 'active' ? 'inactive' : 'active';
        $shift->update(['status' => $newStatus]);

        return redirect()->route('owner.shifts.index')
            ->with('success', 'Status shift berhasil diubah menjadi ' . ($newStatus === 'active' ? 'Aktif' : 'Nonaktif'));
    }

    private function shiftPayload(Shift $shift): array
    {
        $startTime = $this->formatTime($shift->start_time);
        $endTime = $this->formatTime($shift->end_time);
        $breakStart = $this->formatTime($shift->break_start);
        $breakEnd = $this->formatTime($shift->break_end);

        return [
            'id' => $shift->id,
            'name' => $shift->name,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'grace_period' => $shift->grace_period,
            'status' => $shift->status,
            'notes' => $shift->notes,
            'duration_hours' => $startTime && $endTime ? round($this->minutesBetweenShiftTimes($startTime, $endTime) / 60, 2) : 0,
            'break_duration_minutes' => $breakStart && $breakEnd ? $this->minutesBetweenShiftTimes($breakStart, $breakEnd) : null,
            'created_at' => optional($shift->created_at)->format('d F Y H:i'),
            'updated_at' => optional($shift->updated_at)->format('d F Y H:i'),
            'urls' => [
                'show' => route('owner.shifts.show', $shift),
                'edit' => route('owner.shifts.edit', $shift),
                'destroy' => route('owner.shifts.destroy', $shift),
                'toggle_status' => route('owner.shifts.toggle-status', $shift),
            ],
        ];
    }

    private function formatTime($value): ?string
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    private function shiftValidator(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'grace_period' => 'required|integer|min:0|max:60',
            'notes' => 'nullable|string|max:500',
        ];

        if ($isUpdate) {
            $rules['status'] = 'required|in:active,inactive';
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled(['start_time', 'end_time'])) {
                return;
            }

            $shiftMinutes = $this->minutesBetweenShiftTimes($request->start_time, $request->end_time);

            if ($shiftMinutes <= 0) {
                $validator->errors()->add('end_time', 'Jam selesai shift tidak valid.');
                return;
            }

            if ($request->filled('break_end') && !$request->filled('break_start')) {
                $validator->errors()->add('break_start', 'Jam mulai istirahat wajib diisi jika jam selesai istirahat diisi.');
                return;
            }

            if (!$request->filled('break_start')) {
                return;
            }

            $breakStartOffset = $this->minutesFromShiftStart($request->start_time, $request->break_start);

            if ($breakStartOffset > $shiftMinutes) {
                $validator->errors()->add('break_start', 'Jam mulai istirahat harus berada dalam jam shift.');
            }

            if ($request->filled('break_end')) {
                $breakEndOffset = $this->minutesFromShiftStart($request->start_time, $request->break_end);

                if ($breakEndOffset <= $breakStartOffset) {
                    $validator->errors()->add('break_end', 'Jam selesai istirahat harus setelah jam mulai istirahat.');
                }

                if ($breakEndOffset > $shiftMinutes) {
                    $validator->errors()->add('break_end', 'Jam selesai istirahat harus berada dalam jam shift.');
                }
            }
        });

        return $validator;
    }

    private function minutesBetweenShiftTimes(string $startTime, string $endTime): int
    {
        $minutes = $this->timeToMinutes($endTime) - $this->timeToMinutes($startTime);

        return $minutes <= 0 ? $minutes + 1440 : $minutes;
    }

    private function minutesFromShiftStart(string $shiftStart, string $time): int
    {
        $minutes = $this->timeToMinutes($time) - $this->timeToMinutes($shiftStart);

        return $minutes < 0 ? $minutes + 1440 : $minutes;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
