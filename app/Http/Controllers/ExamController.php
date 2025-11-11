<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $query = Exam::with('academicSession');

        // Optional search by name or term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('term', 'like', "%{$search}%")
                  ->orWhereHas('academicSession', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $exams = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('exams.index', compact('exams'));
    }

    /**
     * Show form to create a new exam.
     */
    public function create()
    {
        $sessions = AcademicSession::all();
        return view('exams.create', compact('sessions'));
    }

    /**
     * Store a newly created exam in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        Exam::create($request->only('name', 'term', 'academic_session_id'));

        return redirect()->route('exams.index')->with('success', 'Exam created successfully.');
    }

    /**
     * Show form to edit an existing exam.
     */
    public function edit(Exam $exam)
    {
        $sessions = AcademicSession::all();
        return view('exams.edit', compact('exam', 'sessions'));
    }

    /**
     * Update an existing exam.
     */
    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        $exam->update($request->only('name', 'term', 'academic_session_id'));

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully.');
    }

    /**
     * Delete an exam.
     */
    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }
}
