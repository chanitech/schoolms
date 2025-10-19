<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Permissions
        $this->middleware('permission:view jobcards')->only(['index', 'myJobCards']);
        $this->middleware('permission:create jobcards')->only(['create', 'store']);
        $this->middleware('permission:edit jobcards')->only(['edit', 'update']);
        $this->middleware('permission:delete jobcards')->only(['destroy']);
        $this->middleware('permission:update job status')->only(['updateStatus']);
        $this->middleware('permission:rate jobcards')->only(['rateTask']);
    }

    /**
     * List all job cards (admin view)
     */
    public function index()
    {
        $jobCards = JobCard::with(['assigner', 'assignee'])->latest()->paginate(10);
        return view('jobcards.index', compact('jobCards'));
    }

    /**
     * Show form to create a job card
     */
    public function create()
    {
        $this->authorize('create jobcards');

        $staffs = Staff::all();
        return view('jobcards.create', compact('staffs'));
    }

    /**
     * Store a new job card
     */
    public function store(Request $request)
    {
        $this->authorize('create jobcards');

        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'due_date'    => 'nullable|date',
        ]);

        $assigner = Auth::user()->staff;
        if (!$assigner) {
            return redirect()->back()->with('error', 'You do not have a staff profile.');
        }

        JobCard::create([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_by' => $assigner->id,
            'assigned_to' => $request->assigned_to,
            'status'      => 'pending',
            'due_date'    => $request->due_date,
        ]);

        return redirect()->route('jobcards.index')->with('success', 'Job card created successfully.');
    }

    /**
     * Edit a job card
     */
    public function edit(JobCard $jobcard)
    {
        $this->authorize('edit jobcards');

        $staffs = Staff::all();
        return view('jobcards.edit', compact('jobcard', 'staffs'));
    }

    /**
     * Update a job card
     */
    public function update(Request $request, JobCard $jobcard)
    {
        $this->authorize('edit jobcards');

        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'status'      => 'required|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
        ]);

        $jobcard->update($request->only('title', 'description', 'assigned_to', 'status', 'due_date'));

        return redirect()->route('jobcards.index')->with('success', 'Job card updated successfully.');
    }

    /**
     * Delete a job card
     */
    public function destroy(JobCard $jobcard)
    {
        $this->authorize('delete jobcards');

        $jobcard->delete();
        return redirect()->route('jobcards.index')->with('success', 'Job card deleted successfully.');
    }

    /**
     * Staff: View only their assigned job cards
     */
    public function myJobCards()
    {
        $staff = Auth::user()->staff;
        if (!$staff) {
            return redirect()->back()->with('error', 'You do not have a staff profile.');
        }

        $jobcards = $staff->jobcards()->latest()->paginate(10);

        return view('jobcards.my', compact('jobcards'));
    }

    /**
     * Staff: Update their own job status
     */
    public function updateStatus(Request $request, $jobcardId)
{
    $request->validate([
        'status' => 'required|in:pending,in_progress,completed',
    ]);

    $staff = Auth::user()->staff;
    if (!$staff) {
        return redirect()->back()->with('error', 'No staff profile found.');
    }

    $jobcard = $staff->jobcards()->findOrFail($jobcardId);

    // Assignee can update status anytime
    if ($jobcard->assigned_to !== $staff->id) {
        abort(403, 'Unauthorized');
    }

    $jobcard->update(['status' => $request->status]);

    return redirect()->route('jobcards.my')->with('success', 'Job card status updated.');
}


    /**
     * Assigner (staff) rates a completed task
     */
    public function rateTask(Request $request, $jobcardId)
    {
        $this->authorize('rate jobcards');

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $assigner = Auth::user()->staff;
        if (!$assigner) {
            return redirect()->back()->with('error', 'Staff profile not found.');
        }

        $jobcard = $assigner->assignedJobcards()->findOrFail($jobcardId);

        if ($jobcard->due_date && now()->lt($jobcard->due_date)) {
            return redirect()->back()->with('error', 'Cannot rate task before its due date.');
        }

        $jobcard->update(['rating' => $request->rating]);

        return redirect()->back()->with('success', 'Job card rated successfully.');
    }
}
