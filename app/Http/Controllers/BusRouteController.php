<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusRoute;
use App\Models\BusStop;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\TransportFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusRouteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view transport')->only(['index', 'show']);
        $this->middleware('permission:manage transport')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'storeStop', 'updateStop', 'destroyStop',
            'assignStudent', 'unassignStudent', 'generateFees',
        ]);
    }

    public function index()
    {
        $routes = BusRoute::with(['bus', 'activeAssignments'])->orderBy('name')->get();
        return view('transport.routes.index', compact('routes'));
    }

    public function create()
    {
        $buses = Bus::orderBy('plate_number')->get();
        return view('transport.routes.create', compact('buses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id'      => 'nullable|exists:buses,id',
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'monthly_fee' => 'required|numeric|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        $route = BusRoute::create($data);
        return redirect()->route('transport.routes.show', $route)->with('success', 'Route created — now add stops and assign students.');
    }

    public function show(BusRoute $route)
    {
        $route->load(['bus.driver', 'stops', 'activeAssignments.student', 'activeAssignments.stop']);
        $unassignedStudents = Student::whereDoesntHave('transportAssignment', fn($q) => $q->where('status', 'active'))
            ->orderBy('first_name')->get();

        return view('transport.routes.show', compact('route', 'unassignedStudents'));
    }

    public function edit(BusRoute $route)
    {
        $buses = Bus::orderBy('plate_number')->get();
        return view('transport.routes.edit', compact('route', 'buses'));
    }

    public function update(Request $request, BusRoute $route)
    {
        $data = $request->validate([
            'bus_id'      => 'nullable|exists:buses,id',
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'monthly_fee' => 'required|numeric|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        $route->update($data);
        return redirect()->route('transport.routes.show', $route)->with('success', 'Route updated.');
    }

    public function destroy(BusRoute $route)
    {
        if ($route->activeAssignments()->exists()) {
            return back()->with('error', 'Cannot delete a route with students still assigned — reassign them first.');
        }
        $route->delete();
        return redirect()->route('transport.routes.index')->with('success', 'Route removed.');
    }

    // ── Stops ───────────────────────────────────────────────────────────

    public function storeStop(Request $request, BusRoute $route)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'stop_order'    => 'nullable|integer|min:0',
            'pickup_time'   => 'nullable|date_format:H:i',
            'dropoff_time'  => 'nullable|date_format:H:i',
        ]);

        $route->stops()->create([
            'name'         => $data['name'],
            'stop_order'   => $data['stop_order'] ?? ($route->stops()->max('stop_order') + 1),
            'pickup_time'  => $data['pickup_time'] ?? null,
            'dropoff_time' => $data['dropoff_time'] ?? null,
        ]);

        return back()->with('success', 'Stop added.');
    }

    public function updateStop(Request $request, BusStop $stop)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'stop_order'    => 'nullable|integer|min:0',
            'pickup_time'   => 'nullable|date_format:H:i',
            'dropoff_time'  => 'nullable|date_format:H:i',
        ]);
        $stop->update($data);
        return back()->with('success', 'Stop updated.');
    }

    public function destroyStop(BusStop $stop)
    {
        $stop->delete();
        return back()->with('success', 'Stop removed.');
    }

    // ── Student assignment ─────────────────────────────────────────────

    public function assignStudent(Request $request, BusRoute $route)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'stop_id'    => 'nullable|exists:bus_stops,id',
            'start_date' => 'nullable|date',
        ]);

        $existing = StudentTransportAssignment::where('student_id', $data['student_id'])->first();

        if ($existing) {
            $existing->update([
                'route_id'    => $route->id,
                'stop_id'     => $data['stop_id'] ?? null,
                'status'      => 'active',
                'start_date'  => $data['start_date'] ?? now()->toDateString(),
                'end_date'    => null,
                'assigned_by' => Auth::id(),
            ]);
        } else {
            StudentTransportAssignment::create([
                'student_id'  => $data['student_id'],
                'route_id'    => $route->id,
                'stop_id'     => $data['stop_id'] ?? null,
                'status'      => 'active',
                'start_date'  => $data['start_date'] ?? now()->toDateString(),
                'assigned_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Student assigned to this route.');
    }

    public function unassignStudent(StudentTransportAssignment $assignment)
    {
        $assignment->update(['status' => 'inactive', 'end_date' => now()->toDateString()]);
        return back()->with('success', 'Student removed from the route.');
    }

    // ── Monthly fee generation ─────────────────────────────────────────

    /**
     * Create this month's transport fee for every actively-assigned
     * student on the route. Idempotent — the unique(student_id, month,
     * year) constraint means re-running just skips students already billed.
     */
    public function generateFees(Request $request, BusRoute $route)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        if ($route->monthly_fee <= 0) {
            return back()->with('error', 'This route has no monthly fee set — edit the route first.');
        }

        $created = 0;
        DB::transaction(function () use ($route, $month, $year, &$created) {
            // Query fresh rather than trust $route's relation cache, which
            // may be stale if a prior action on this same request/object
            // already eager-loaded activeAssignments before this student
            // was assigned.
            $activeAssignments = StudentTransportAssignment::where('route_id', $route->id)
                ->where('status', 'active')->get();

            foreach ($activeAssignments as $assignment) {
                $exists = TransportFee::where('student_id', $assignment->student_id)
                    ->where('month', $month)->where('year', $year)->exists();
                if ($exists) continue;

                TransportFee::create([
                    'student_id' => $assignment->student_id,
                    'route_id'   => $route->id,
                    'month'      => $month,
                    'year'       => $year,
                    'amount'     => $route->monthly_fee,
                    'balance'    => $route->monthly_fee,
                    'status'     => 'unpaid',
                    'due_date'   => now()->createFromDate($year, $month, 1)->endOfMonth(),
                ]);
                $created++;
            }
        });

        return back()->with('success', "Generated {$created} fee record(s) for " . now()->createFromDate($year, $month, 1)->format('F Y') . '.');
    }
}
