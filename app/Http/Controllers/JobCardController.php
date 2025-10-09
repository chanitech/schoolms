<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobCardController extends Controller
{
    /**
     * List all job cards (for admin-like view or staff overview)
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
        $staffs = Staff::all();
        return view('jobcards.create', compact('staffs'));
    }

    /**
     * Store a new job card (assigned by staff)
     */
    public function store(Request $request)
    {
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
        $staffs = Staff::all();
        return view('jobcards.edit', compact('jobcard', 'staffs'));
    }

    /**
     * Update a job card
     */
    public function update(Request $request, JobCard $jobcard)
    {
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

        if ($jobcard->due_date && now()->lt($jobcard->due_date) && $request->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot mark as completed before due date.');
        }

        $jobcard->update(['status' => $request->status]);

        return redirect()->route('jobcards.my')->with('success', 'Job card status updated.');
    }

    /**
     * Assigner (staff) rates a completed task
     */
    public function rateTask(Request $request, $jobcardId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $assigner = Auth::user()->staff;

        if (!$assigner) {
            return redirect()->back()->with('error', 'Staff profile not found.');
        }

        // Only the staff who assigned the task can rate
        $jobcard = $assigner->assignedJobcards()->findOrFail($jobcardId);

        if ($jobcard->due_date && now()->lt($jobcard->due_date)) {
            return redirect()->back()->with('error', 'Cannot rate task before its due date.');
        }

        $jobcard->update(['rating' => $request->rating]);

        return redirect()->back()->with('success', 'Job card rated successfully.');
    }
}
