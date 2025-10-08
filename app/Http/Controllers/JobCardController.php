<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobCardController extends Controller
{
    public function index()
    {
        $jobCards = JobCard::with(['assigner', 'assignee'])->paginate(10);
        return view('jobcards.index', compact('jobCards'));
    }

    public function create()
    {
        $staffs = Staff::all();
        return view('jobcards.create', compact('staffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'due_date'    => 'nullable|date',
        ]);

        JobCard::create([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'status'      => 'pending',
            'due_date'    => $request->due_date,
        ]);

        return redirect()->route('jobcards.index')->with('success', 'Job card created successfully.');
    }

    public function edit(JobCard $jobcard)
    {
        $staffs = Staff::all();
        return view('jobcards.edit', compact('jobcard', 'staffs'));
    }

    public function update(Request $request, JobCard $jobcard)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'status'      => 'required|in:pending,in_progress,completed',
            'rating'      => 'nullable|integer|min:1|max:5',
            'due_date'    => 'nullable|date',
        ]);

        $jobcard->update($request->only('title','description','assigned_to','status','rating','due_date'));

        return redirect()->route('jobcards.index')->with('success', 'Job card updated successfully.');
    }

    public function destroy(JobCard $jobcard)
    {
        $jobcard->delete();
        return redirect()->route('jobcards.index')->with('success', 'Job card deleted successfully.');
    }
}
