<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Staff;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeavesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view leaves')->only(['index', 'received']);
        $this->middleware('permission:create leaves')->only(['create', 'store']);
        $this->middleware('permission:edit leaves')->only(['edit', 'update']);
        $this->middleware('permission:delete leaves')->only(['destroy']);
        $this->middleware('permission:approve received leaves')->only(['approve', 'reject']);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Leave::with(['requester.department'])->orderByDesc('start_date');

        if ($user->staff) {
            $query->where('staff_id', $user->staff->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        if ($request->filled('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
        }

        $leaves = $query->paginate(10)->withQueryString();

        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff) {
            abort(403, 'You do not have a staff profile to request leave.');
        }

        $recipients = Staff::whereHas('user.roles', function ($q) {
            $q->whereIn('name', ['hod', 'director']);
        })->orderBy('first_name')->get();

        return view('leaves.create', compact('recipients', 'staff'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff) {
            abort(403, 'You do not have a staff profile to request leave.');
        }

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
                         ->with('success', 'Leave request submitted successfully.');
    }

    public function edit(Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff || $leave->staff_id !== $staff->id || $leave->status !== 'pending') {
            abort(403, 'You are not authorized to edit this leave.');
        }

        $recipients = Staff::whereHas('user.roles', function ($q) {
            $q->whereIn('name', ['hod', 'director']);
        })->orderBy('first_name')->get();

        return view('leaves.edit', compact('leave', 'recipients'));
    }

    public function update(Request $request, Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff || $leave->staff_id !== $staff->id || $leave->status !== 'pending') {
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

    public function destroy(Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff || $leave->staff_id !== $staff->id || $leave->status !== 'pending') {
            abort(403, 'You are not authorized to delete this leave.');
        }

        $leave->delete();

        return redirect()->route('leaves.index')
                         ->with('success', 'Leave deleted successfully.');
    }

    public function received(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Leave::with(['requester.department']);

        if ($user->hasRole('admin') || $user->hasRole('director')) {
            // admin and director can see all
        } elseif ($user->hasRole('hod') && $user->staff) {
            $departmentId = $user->staff->department_id;
            $query->whereHas('requester', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        } elseif ($user->staff) {
            $query->where('requested_to', $user->staff->id);
        } else {
            abort(403, 'You are not authorized to view this page.');
        }

        // Filters
        if ($request->filled('department_id')) {
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('staff_name')) {
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->staff_name.'%')
                  ->orWhere('last_name', 'like', '%'.$request->staff_name.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        if ($request->filled('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('leaves.received', compact('leaves', 'departments'));
    }

    public function approve(Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (($staff && $leave->requested_to !== $staff->id) && !$user->hasRole('admin') && !$user->hasRole('director')) {
            abort(403, 'You are not authorized to approve this leave.');
        }

        $leave->update(['status' => 'approved']);

        return redirect()->route('leaves.received')->with('success', 'Leave approved successfully.');
    }

    public function reject(Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (($staff && $leave->requested_to !== $staff->id) && !$user->hasRole('admin') && !$user->hasRole('director')) {
            abort(403, 'You are not authorized to reject this leave.');
        }

        $leave->update(['status' => 'rejected']);

        return redirect()->route('leaves.received')->with('success', 'Leave rejected successfully.');
    }

    public function exportReceivedExcel(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $staffId = $user->staff->id ?? null;

        if (!$user->hasRole('admin') && !$user->hasRole('director') && !$user->hasRole('hod') && !$staffId) {
            abort(403, 'Unauthorized');
        }

        $filters = $request->all();

        return Excel::download(new LeavesExport($staffId, $filters), 'received_leaves.xlsx');
    }

    public function exportReceivedPdf(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $staff = $user->staff;

        if (!$user->hasRole('admin') && !$user->hasRole('director') && !$user->hasRole('hod') && !$staff) {
            abort(403, 'Unauthorized');
        }

        $leavesQuery = Leave::with(['requester.department']);

        if ($user->hasRole('hod') && $staff) {
            $departmentId = $staff->department_id;
            $leavesQuery->whereHas('requester', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($request->filled('department_id')) {
            $leavesQuery->whereHas('requester', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('status')) {
            $leavesQuery->where('status', $request->status);
        }
        if ($request->filled('start_date_from')) {
            $leavesQuery->where('start_date', '>=', $request->start_date_from);
        }
        if ($request->filled('start_date_to')) {
            $leavesQuery->where('start_date', '<=', $request->start_date_to);
        }

        $leaves = $leavesQuery->orderBy('start_date', 'desc')->get();

        $pdf = Pdf::loadView('leaves.received_pdf', compact('leaves'));
        return $pdf->download('received_leaves.pdf');
    }
}
