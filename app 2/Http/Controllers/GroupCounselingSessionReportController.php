<?php

namespace App\Http\Controllers;

use App\Models\GroupCounselingSessionReport;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupCounselingSessionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all group session reports
     */
    public function index()
    {
        $reports = GroupCounselingSessionReport::latest()->paginate(15);
        return view('counseling.group.index', compact('reports'));
    }

    /**
     * Show form to create a new report
     */
    public function create()
    {
        $students = Student::all();
        return view('counseling.group.create', compact('students'));
    }

    /**
     * Store a new group session report
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'group_name' => 'required|string|max:255',
            'students' => 'nullable|array',
            'date' => 'required|date',
            'time' => 'required',
            'session_number' => 'nullable|integer',
            'presenting_problem' => 'nullable|string',
            'work_done' => 'nullable|string',
            'assessment_progress' => 'nullable|string',
            'intervention_plan' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'biopsychosocial_formulation' => 'nullable|array',
        ]);

        $data['biopsychosocial_formulation'] = $request->biopsychosocial_formulation ?? [];
        $data['user_id'] = Auth::id();
        $data['members'] = $request->students ?? [];

        $report = GroupCounselingSessionReport::create($data);

        if ($request->has('students')) {
            $report->students()->sync($request->students);
        }

        return redirect()->route('counseling.group.index')
                         ->with('success', 'Group session report created successfully.');
    }

    /**
     * Show a single report
     */
    public function show(GroupCounselingSessionReport $group)
    {
        // Use $group as variable to match route model binding {group}
        return view('counseling.group.show', compact('group'));
    }

    /**
     * Show form to edit a report
     */
    public function edit(GroupCounselingSessionReport $group)
{
    $students = Student::all();
    $selectedStudents = $group->students->pluck('id')->toArray();

    return view('counseling.group.edit', [
        'groupCounselingSessionReport' => $group,
        'students' => $students,
        'selectedStudents' => $selectedStudents
    ]);
}


    /**
     * Update a report
     */
    public function update(Request $request, GroupCounselingSessionReport $group)
    {
        $data = $request->validate([
            'group_name' => 'required|string|max:255',
            'students' => 'nullable|array',
            'date' => 'required|date',
            'time' => 'required',
            'session_number' => 'nullable|integer',
            'presenting_problem' => 'nullable|string',
            'work_done' => 'nullable|string',
            'assessment_progress' => 'nullable|string',
            'intervention_plan' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'biopsychosocial_formulation' => 'nullable|array',
        ]);

        $data['biopsychosocial_formulation'] = $request->biopsychosocial_formulation ?? [];
        $data['members'] = $request->students ?? [];

        $group->update($data);

        if ($request->has('students')) {
            $group->students()->sync($request->students);
        }

        return redirect()->route('counseling.group.index')
                         ->with('success', 'Group session report updated successfully.');
    }

    /**
     * Delete a report
     */
    public function destroy(GroupCounselingSessionReport $group)
    {
        $group->delete();
        return back()->with('success', 'Group session report deleted successfully.');
    }
}
