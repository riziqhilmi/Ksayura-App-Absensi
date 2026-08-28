<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('created_at', 'desc')->get();
        $activeShifts = Shift::where('status', 'active')->count();
        $inactiveShifts = Shift::where('status', 'inactive')->count();
        
        return view('owner.shifts.index', compact('shifts', 'activeShifts', 'inactiveShifts'));
    }

    public function create()
    {
        return view('owner.shifts.create');
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
        return view('owner.shifts.show', compact('shift'));
    }

    public function edit(Shift $shift)
    {
        return view('owner.shifts.edit', compact('shift'));
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
}