<?php

namespace App\Http\Controllers;

use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetablePeriodController extends Controller
{
    public function __construct()
    {
        // Matches TimetableController's own edit/update gate — Period
        // Settings is the same admin-facing configuration surface, just
        // for the periods that generation and capacity analysis depend on.
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->hasAnyRole(['Admin', 'Academic'])) {
                abort(403, 'Only Admins and Academic staff can manage timetable periods.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $periods = TimetablePeriod::orderBy('order_no')->get();

        return view('timetable-periods.index', compact('periods'));
    }

    public function create()
    {
        $nextOrder = (int) (TimetablePeriod::max('order_no') ?? 0) + 1;

        return view('timetable-periods.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:60',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'order_no'   => 'required|integer|min:0|max:255',
        ]);

        TimetablePeriod::create([
            'name'       => $data['name'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'is_break'   => $request->boolean('is_break'),
            'is_active'  => $request->boolean('is_active', true),
            'order_no'   => $data['order_no'],
        ]);

        return redirect()->route('timetable-periods.index')->with('success', 'Period created successfully.');
    }

    public function edit(TimetablePeriod $timetablePeriod)
    {
        return view('timetable-periods.edit', compact('timetablePeriod'));
    }

    public function update(Request $request, TimetablePeriod $timetablePeriod)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:60',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'order_no'   => 'required|integer|min:0|max:255',
        ]);

        $timetablePeriod->update([
            'name'       => $data['name'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'is_break'   => $request->boolean('is_break'),
            'is_active'  => $request->boolean('is_active', true),
            'order_no'   => $data['order_no'],
        ]);

        return redirect()->route('timetable-periods.index')->with('success', 'Period updated successfully.');
    }

    public function destroy(TimetablePeriod $timetablePeriod)
    {
        $inUse = TimetableEntry::where('period_id', $timetablePeriod->id)->exists();
        if ($inUse) {
            return redirect()->route('timetable-periods.index')
                ->with('error', "Can't delete \"{$timetablePeriod->name}\" — it's used by existing timetable entries. Deactivate it instead.");
        }

        $timetablePeriod->delete();

        return redirect()->route('timetable-periods.index')->with('success', 'Period deleted.');
    }
}
