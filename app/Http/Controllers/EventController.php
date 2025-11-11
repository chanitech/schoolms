<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventsExport;
use Barryvdh\DomPDF\Facade\Pdf;


class EventController extends Controller
{
    /**
     * Display a listing of events with summary and filters.
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $events = $query->orderBy('start_date', 'asc')->paginate(10);

        // Summary counts
        $summary = [
            'total' => Event::count(),
            'academic' => Event::where('type', 'academic')->count(),
            'sport' => Event::where('type', 'sport')->count(),
            'cultural' => Event::where('type', 'cultural')->count(),
            'holiday' => Event::where('type', 'holiday')->count(),
            'other' => Event::where('type', 'other')->count(),
        ];

        $departments = Department::all();

        return view('events.index', compact('events', 'summary', 'departments'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $departments = Department::all();
        return view('events.create', compact('departments'));
    }


    

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:academic,sport,cultural,holiday,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        Event::create([
            'title' => $request->title,
            'department_id' => $request->department_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'created_by' => Auth::user()->staff->id,
        ]);

        return redirect()->route('events.index')
                         ->with('success', 'Event created successfully.');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $departments = Department::all();
        return view('events.edit', compact('event', 'departments'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:academic,sport,cultural,holiday,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $event->update($request->all());

        return redirect()->route('events.index')
                         ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')
                         ->with('success', 'Event deleted successfully.');
    }

    /**
     * Show the calendar page.
     */
    

    public function calendar()
{
    $events = Event::with('department')->get()->map(function($event) {
        return [
            'id' => $event->id,
            'title' => $event->title . ($event->department ? ' (' . $event->department->name . ')' : ''),
            'start' => $event->start_date,
            'end' => $event->end_date,
            'url' => route('events.edit', $event), // optional: open edit page on click
            'color' => match($event->type) {
                'academic' => '#007bff',
                'sport' => '#28a745',
                'cultural' => '#ffc107',
                'holiday' => '#dc3545',
                default => '#6c757d'
            }
        ];
    });

    return view('events.calendar', compact('events'));
}


    /**
     * Fetch events for FullCalendar (JSON).
     */
    public function fetchEvents()
{
    $events = Event::with('department')->get();

    $formatted = $events->map(function($event) {
        // Assign color based on type
        $color = match($event->type) {
            'academic' => '#28a745', // green
            'sport' => '#ffc107',    // yellow
            'cultural' => '#007bff', // blue
            'holiday' => '#dc3545',  // red
            'other' => '#6c757d',    // gray
            default => '#17a2b8',    // teal
        };

        return [
            'id'    => $event->id,
            'title' => $event->title . ($event->department ? ' (' . $event->department->name . ')' : ''),
            'start' => $event->start_date,
            'end'   => $event->end_date,
            'url'   => route('events.edit', $event),
            'color' => $color,
        ];
    });

    return response()->json($formatted);
}



    /**
     * Determine event color based on type.
     */
    private function getEventColor($type)
    {
        return match($type) {
            'academic' => 'blue',
            'sport' => 'green',
            'cultural' => 'orange',
            'holiday' => 'red',
            'other' => 'gray',
            default => 'black',
        };
    }

    /**
     * Export events to Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }

    /**
     * Export events to PDF.
     */
    public function exportPDF()
{
    $events = Event::with('department')->get();
    $pdf = Pdf::loadView('events.pdf', compact('events'));
    return $pdf->download('events.pdf');
}
}
