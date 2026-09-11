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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after:start_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'grace_period' => 'required|integer|min:0|max:60',
            'notes' => 'nullable|string|max:500',
        ]);

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after:start_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'grace_period' => 'required|integer|min:0|max:60',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:500',
        ]);

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
            'duration_hours' => $startTime && $endTime ? Carbon::parse($startTime)->diffInHours(Carbon::parse($endTime)) : 0,
            'break_duration_minutes' => $breakStart && $breakEnd ? Carbon::parse($breakStart)->diffInMinutes(Carbon::parse($breakEnd)) : null,
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
}
