<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Enrollment;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $exams = Exam::all();

        // Only allow teachers to see their subjects
        if ($user->hasRole('Teacher')) {
            $subjects = Subject::where('teacher_id', $user->id)->get();
        } else {
            $subjects = Subject::all();
        }

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
            if ($user->hasRole('Teacher')) {
                $marksQuery->where('subject_id', $request->subject_id)
                           ->whereHas('subject', fn($q) => $q->where('teacher_id', $user->id));
            } else {
                $marksQuery->where('subject_id', $request->subject_id);
            }
        } elseif ($user->hasRole('Teacher')) {
            $marksQuery->whereHas('subject', fn($q) => $q->where('teacher_id', $user->id));
        }

        $marks = $marksQuery->orderBy('created_at', 'desc')->paginate(20);

        return view('marks.index', compact('marks', 'sessions', 'classes', 'subjects', 'exams'));
    }

    /**
     * Show the form for creating marks.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $exams = Exam::all();

        if ($user->hasRole('Teacher')) {
            $subjects = Subject::where('teacher_id', $user->id)->get();
        } else {
            $subjects = Subject::all();
        }

        return view('marks.create', compact('sessions', 'classes', 'subjects', 'exams'));
    }

    /**
     * Store newly created marks.
     */
  public function store(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'class_id' => 'required|exists:school_classes,id',
        'subject_id' => 'required|exists:subjects,id',
        'exam_id' => 'required|exists:exams,id',
        'marks' => 'required|array',
        'marks.*' => 'required|numeric|min:0|max:100',
    ]);

    if ($user->hasRole('Teacher')) {
        $subject = Subject::where('id', $request->subject_id)
                          ->where('teacher_id', $user->id)
                          ->firstOrFail();
    }

    foreach ($request->marks as $student_id => $markValue) {

        // Check if the student is enrolled and not withdrawn from this subject
        $enrollment = Enrollment::where('student_id', $student_id)
            ->where('class_id', $request->class_id)
            ->where('academic_session_id', $request->academic_session_id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            continue; // Skip students not actively enrolled
        }

        $student = $enrollment->student;

        // Skip student if withdrawn from this subject
        if ($student->subjects()
                    ->where('subject_id', $request->subject_id)
                    ->wherePivot('withdrawn', 1)
                    ->exists()) {
            continue;
        }

        $grade = Grade::where('min_mark', '<=', $markValue)
                      ->where('max_mark', '>=', $markValue)
                      ->first();

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
                'class_id' => $request->class_id,
            ]
        );
    }

    return redirect()->route('marks.index')->with('success', 'Marks saved successfully with grades!');
}


    /**
     * AJAX: Get students of a class and session (enrolled only).
     */
    /**
 * AJAX: Get students of a class, session, and subject (enrolled only and not withdrawn)
 */
public function getStudents(Request $request)
{
    $request->validate([
        'class_id' => 'required|exists:school_classes,id',
        'session_id' => 'required|exists:academic_sessions,id',
        'subject_id' => 'required|exists:subjects,id', // add this
    ]);

    // Fetch enrollments for the class & session
    $enrollments = Enrollment::with('student')
        ->where('class_id', $request->class_id)
        ->where('academic_session_id', $request->session_id)
        ->where('status', 'active')
        ->get();

    $students = [];

    foreach ($enrollments as $enrollment) {
        $student = $enrollment->student;

        // Skip if student is withdrawn from this subject
        $isWithdrawn = $student->subjects()
                               ->where('subject_id', $request->subject_id)
                               ->wherePivot('withdrawn', 1)
                               ->exists();

        if (!$isWithdrawn) {
            $students[] = [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
            ];
        }
    }

    return response()->json($students);
}



    /**
     * Show the form for editing a mark.
     */
    public function edit(Mark $mark)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $sessions = AcademicSession::all();
        $exams = Exam::all();

        if ($user->hasRole('Teacher')) {
            $subjects = Subject::where('teacher_id', $user->id)->get();
        } else {
            $subjects = Subject::all();
        }

        return view('marks.edit', compact('mark', 'sessions', 'subjects', 'exams'));
    }

    /**
     * Update the specified mark.
     */
    public function update(Request $request, Mark $mark)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'mark' => 'required|numeric|min:0|max:100',
        ]);

        if ($user->hasRole('Teacher')) {
            $subject = Subject::where('id', $request->subject_id)
                              ->where('teacher_id', $user->id)
                              ->firstOrFail();
        }

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
     * Delete a mark.
     */
    public function destroy(Mark $mark)
    {
        $mark->delete();
        return redirect()->route('marks.index')->with('success', 'Mark deleted successfully!');
    }
}
