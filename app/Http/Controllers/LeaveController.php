<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeavesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveController extends Controller
{
    /**
     * Display a listing of the logged-in user's leave requests.
     */
    public function index()
    {
        $user = Auth::user();
        $staff = $user->staff;

        // Get all leaves requested by the logged-in staff
        $leaves = Leave::where('staff_id', $staff->id)
                       ->orderByDesc('start_date')
                       ->paginate(10);

        return view('leaves.index', compact('leaves'));
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create()
    {
        $user = Auth::user();
        $staff = $user->staff;

        // Staff can select to send to HOD or Director
        // Example: all staff with role = 'hod' or 'director'
        $recipients = Staff::whereIn('role', ['hod', 'director'])
                           ->orderBy('first_name')
                           ->get();

        return view('leaves.create', compact('recipients', 'staff'));
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $staff = $user->staff;

        $request->validate([
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'type'         => 'required|in:sick,casual,annual,other',
            'reason'       => 'nullable|string|max:500',
            'requested_to' => 'required|exists:staff,id',
        ]);

        Leave::create([
            'staff_id'     => $staff->id,
            'requested_to' => $request->requested_to,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'type'         => $request->type,
            'status'       => 'pending',
            'reason'       => $request->reason,
        ]);

        return redirect()->route('leaves.index')
                         ->with('success', 'Leave request submitted successfully and is pending approval.');
    }

    /**
     * Show the form for editing a pending leave request.
     */
    public function edit(Leave $leave)
    {
        $user = Auth::user();

        // Only the owner of a pending leave can edit
        if ($leave->staff_id !== $user->staff->id || $leave->status !== 'pending') {
            abort(403, 'You are not authorized to edit this leave.');
        }

        $recipients = Staff::whereIn('role', ['hod', 'director'])
                           ->orderBy('first_name')
                           ->get();

        return view('leaves.edit', compact('leave', 'recipients'));
    }

    /**
     * Update the specified pending leave request.
     */
    public function update(Request $request, Leave $leave)
    {
        $user = Auth::user();

        if ($leave->staff_id !== $user->staff->id || $leave->status !== 'pending') {
            abort(403, 'You are not authorized to update this leave.');
        }

        $request->validate([
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'type'         => 'required|in:sick,casual,annual,other',
            'reason'       => 'nullable|string|max:500',
            'requested_to' => 'required|exists:staff,id',
        ]);

        $leave->update([
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'type'         => $request->type,
            'reason'       => $request->reason,
            'requested_to' => $request->requested_to,
        ]);

        return redirect()->route('leaves.index')
                         ->with('success', 'Leave updated successfully.');
    }

    /**
     * Remove a pending leave request.
     */
    public function destroy(Leave $leave)
    {
        $user = Auth::user();

        if ($leave->staff_id !== $user->staff->id || $leave->status !== 'pending') {
            abort(403, 'You are not authorized to delete this leave.');
        }

        $leave->delete();

        return redirect()->route('leaves.index')
                         ->with('success', 'Leave deleted successfully.');
    }




    /**
 * Display leaves received by the logged-in staff (HOD/Director).
 */
    /**
 * Display leaves received by the logged-in user (HOD/Director).
 */
public function received(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $staff = $user->staff;

    // Base query: leaves sent to current user
    $query = Leave::with('requester')
                  ->where('requested_to', $staff->id);

    // Filter: staff name
    if ($request->filled('staff_name')) {
        $query->whereHas('requester', function($q) use ($request) {
            $q->where('first_name', 'like', '%'.$request->staff_name.'%')
              ->orWhere('last_name', 'like', '%'.$request->staff_name.'%');
        });
    }

    // Filter: start_date range
    if ($request->filled('start_date_from')) {
        $query->where('start_date', '>=', $request->start_date_from);
    }
    if ($request->filled('start_date_to')) {
        $query->where('start_date', '<=', $request->start_date_to);
    }

    // Filter: status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $leaves = $query->orderBy('start_date', 'desc')
                    ->paginate(10)
                    ->withQueryString();

    // Summary counts
    $summary = Leave::where('requested_to', $staff->id)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

    return view('leaves.received', compact('leaves', 'summary'));
}



/**
 * Approve a leave request.
 */
public function approve(Leave $leave)
{
    $user = Auth::user();
    if ($leave->requested_to !== $user->staff->id || $leave->status !== 'pending') {
        abort(403, 'You are not authorized to approve this leave.');
    }

    $leave->update(['status' => 'approved']);

    return redirect()->route('leaves.received')->with('success', 'Leave approved successfully.');
}

/**
 * Reject a leave request.
 */
public function reject(Leave $leave)
{
    $user = Auth::user();
    if ($leave->requested_to !== $user->staff->id || $leave->status !== 'pending') {
        abort(403, 'You are not authorized to reject this leave.');
    }

    $leave->update(['status' => 'rejected']);

    return redirect()->route('leaves.received')->with('success', 'Leave rejected successfully.');
}


// Excel export for received leaves
    public function exportReceivedExcel(Request $request)
    {
        $user = Auth::user();
        $staff = $user->staff;

        $fileName = 'received_leaves.xlsx';
        return Excel::download(new LeavesExport($staff->id, $request->all()), $fileName);
    }

    // PDF export for received leaves
    public function exportReceivedPdf(Request $request)
    {
        $user = Auth::user();
        $staff = $user->staff;

        $leavesQuery = Leave::with('requester')->where('requested_to', $staff->id);

        // Apply filters
        if (!empty($request->staff_name)) {
            $leavesQuery->whereHas('requester', fn($q) => 
                $q->where('first_name', 'like', '%'.$request->staff_name.'%')
                  ->orWhere('last_name', 'like', '%'.$request->staff_name.'%')
            );
        }
        if (!empty($request->start_date_from)) $leavesQuery->where('start_date', '>=', $request->start_date_from);
        if (!empty($request->start_date_to)) $leavesQuery->where('start_date', '<=', $request->start_date_to);
        if (!empty($request->status)) $leavesQuery->where('status', $request->status);

        $leaves = $leavesQuery->get();
        $pdf = Pdf::loadView('leaves.received_pdf', compact('leaves'));
        return $pdf->download('received_leaves.pdf');
    }

}
