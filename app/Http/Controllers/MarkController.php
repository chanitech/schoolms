<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\AcademicSession;
use App\Models\Grade;

class MarkController extends Controller
{
    /**
     * Display a listing of marks.
     */
    public function index(Request $request)
    {
        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $exams = Exam::all();

        $marksQuery = Mark::with(['student.class', 'subject', 'exam', 'grade']);

        if ($request->filled('academic_session_id')) {
            $marksQuery->where('academic_session_id', $request->academic_session_id);
        }

        if ($request->filled('class_id')) {
            $marksQuery->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('subject_id')) {
            $marksQuery->where('subject_id', $request->subject_id);
        }

        $marks = $marksQuery->orderBy('created_at', 'desc')->paginate(20);

        return view('marks.index', compact('marks', 'sessions', 'classes', 'subjects', 'exams'));
    }

    /**
     * Show the form for creating marks.
     */
    public function create()
    {
        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $exams = Exam::all();

        return view('marks.create', compact('sessions', 'classes', 'subjects', 'exams'));
    }

    /**
     * Store newly created marks.
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'marks' => 'required|array',
            'marks.*' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->marks as $student_id => $markValue) {
            // 🔹 Find the correct grade for this mark
            $grade = Grade::where('min_mark', '<=', $markValue)
                ->where('max_mark', '>=', $markValue)
                ->first();

            // 🔹 Find student class
            $student = Student::find($student_id);

            // 🔹 Save mark with auto-grade and class
            Mark::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'subject_id' => $request->subject_id,
                    'exam_id' => $request->exam_id,
                    'academic_session_id' => $request->academic_session_id,
                ],
                [
                    'mark' => $markValue,
                    'grade_id' => $grade?->id,
                    'class_id' => $student?->class_id,
                ]
            );
        }

        return redirect()->route('marks.index')->with('success', 'Marks saved successfully with grades!');
    }

    /**
     * AJAX: Get students of a class and session.
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $students = Student::where('class_id', $request->class_id)
            ->where('academic_session_id', $request->session_id)
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($students);
    }

    /**
     * Show the form for editing the specified mark.
     */
    public function edit(Mark $mark)
    {
        $sessions = AcademicSession::all();
        $subjects = Subject::all();
        $exams = Exam::all();

        return view('marks.edit', compact('mark', 'sessions', 'subjects', 'exams'));
    }

    /**
     * Update the specified mark.
     */
    public function update(Request $request, Mark $mark)
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'mark' => 'required|numeric|min:0|max:100',
        ]);

        // 🔹 Determine grade
        $grade = Grade::where('min_mark', '<=', $request->mark)
            ->where('max_mark', '>=', $request->mark)
            ->first();

        $mark->update([
            'academic_session_id' => $request->academic_session_id,
            'subject_id' => $request->subject_id,
            'exam_id' => $request->exam_id,
            'mark' => $request->mark,
            'grade_id' => $grade?->id,
        ]);

        return redirect()->route('marks.index')->with('success', 'Mark updated successfully with grade!');
    }

    /**
     * Remove the specified mark.
     */
    public function destroy(Mark $mark)
    {
        $mark->delete();
        return redirect()->route('marks.index')->with('success', 'Mark deleted successfully!');
    }
}
