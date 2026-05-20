<?php

namespace App\Http\Controllers;

use App\Models\IndividualSessionReport;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndividualSessionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the reports
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $reports = IndividualSessionReport::with('student')
            ->when($search, function($query, $search) {
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return view('counseling.individual.index', compact('reports'));
    }

    /**
     * Show form to create a new report
     */
    public function create()
    {
        $students = Student::all();
        return view('counseling.individual.create', compact('students'));
    }

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
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

        $data['user_id'] = Auth::id();
        $data['biopsychosocial_formulation'] = $request->biopsychosocial_formulation ?? [];

        IndividualSessionReport::create($data);

        return redirect()->route('counseling.individual.index')
                         ->with('success', 'Individual session report created successfully.');
    }

    /**
     * Show a single report
     */
    public function show(IndividualSessionReport $individualSessionReport)
    {
        return view('counseling.individual.show', compact('individualSessionReport'));
    }

    /**
     * Show form to edit a report
     */
    public function edit(IndividualSessionReport $individualSessionReport)
    {
        $students = Student::all();
        return view('counseling.individual.edit', compact('individualSessionReport', 'students'));
    }

    /**
     * Update a report
     */
    public function update(Request $request, IndividualSessionReport $individualSessionReport)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
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

        $individualSessionReport->update($data);

        return redirect()->route('counseling.individual.index')
                         ->with('success', 'Individual session report updated successfully.');
    }

    /**
     * Delete a report
     */
    public function destroy(IndividualSessionReport $individualSessionReport)
    {
        $individualSessionReport->delete();
        return back()->with('success', 'Individual session report deleted successfully.');
    }
}
