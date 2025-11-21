<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    public function index(Request $request)
{
    $attendances = Attendance::with('staff')->orderBy('date', 'desc')->paginate(10);

    // Default date: today, or use filter date_from
    $date = $request->date_from ?? now()->toDateString();

    $summary = Attendance::whereDate('date', $date)
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count','status')
        ->toArray();

    return view('attendance.index', compact('attendances', 'summary', 'date'));
}


    public function create()
    {
        $staff = Staff::orderBy('first_name')->get();
        return view('attendance.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,leave',
        ]);

        Attendance::updateOrCreate(
            ['staff_id' => $request->staff_id, 'date' => $request->date],
            ['status' => $request->status]
        );

        return redirect()->route('attendance.index')->with('success', 'Attendance saved.');
    }

    public function edit(Attendance $attendance)
    {
        $staff = Staff::orderBy('first_name')->get();
        return view('attendance.edit', compact('attendance', 'staff'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,leave',
        ]);

        $attendance->update($request->only('staff_id', 'date', 'status'));

        return redirect()->route('attendance.index')->with('success', 'Attendance updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Attendance deleted.');
    }

    // Bulk attendance
    public function bulkCreate()
    {
        $staff = Staff::orderBy('first_name')->get();
        return view('attendance.bulk', compact('staff'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        foreach ($request->attendance as $staff_id => $status) {
            Attendance::updateOrCreate(
                ['staff_id' => $staff_id, 'date' => $request->date],
                ['status' => $status]
            );
        }

        return redirect()->route('attendance.index')->with('success', 'Bulk attendance saved.');
    }

    // Filter
    public function filter(Request $request)
    {
        $query = Attendance::with('staff')->orderBy('date', 'desc');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('staff_name')) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->staff_name}%"]);
            });
        }

        $attendances = $query->paginate(10)->appends($request->all());

        return view('attendance.index', compact('attendances'));
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        return Excel::download(new AttendanceExport($request->all()), 'attendance.xlsx');
    }

    // Export PDF
    public function exportPDF(Request $request)
    {
        $filters = $request->all();
        $query = Attendance::with('staff')->orderBy('date', 'desc');

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['staff_name'])) {
            $query->whereHas('staff', function ($q) use ($filters) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$filters['staff_name']}%"]);
            });
        }

        $attendances = $query->get();
        $pdf = Pdf::loadView('attendance.export_pdf', compact('attendances'));
        return $pdf->download('attendance.pdf');
    }
}
