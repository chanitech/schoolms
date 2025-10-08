<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\AcademicSession;
use App\Models\SchoolClass; 
use App\Services\StudentResultService;
use Illuminate\Support\Facades\Log;

class MarkController extends Controller
{
    /**
     * Display all marks with optional class filter.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::all();

        $marksQuery = Mark::with(['student.class','subject','exam','grade']);

        // Optional filter: only marks for selected class
        if ($request->filled('class_id')) {
            $marksQuery->whereHas('student', function($q) use($request){
                $q->where('class_id', $request->class_id);
            });
        }

        $marks = $marksQuery->paginate(10)->withQueryString();

        return view('marks.index', compact('marks','classes'));
    }

    /**
     * Show form to create new marks.
     */
    public function create()
    {
        $subjects = Subject::all();
        $exams    = Exam::all();
        $grades   = Grade::all();
        $sessions = AcademicSession::all();
        $classes  = SchoolClass::all();

        return view('marks.create', compact('subjects','exams','grades','sessions','classes'));
    }

    /**
     * AJAX: Get students based on class & session.
     */
    public function getStudents(Request $request)
{
    $request->validate([
        'class_id' => 'required|exists:school_classes,id',
        'session_id' => 'required|exists:academic_sessions,id',
    ]);

    try {
        $students = Student::select('students.id', 'students.first_name', 'students.last_name')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.class_id', $request->class_id)
            ->where('enrollments.academic_session_id', $request->session_id)
            ->where('enrollments.status', 'active')
            ->get();

        return response()->json($students);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load students. ' . $e->getMessage()
        ], 500);
    }
}



    /**
     * Store multiple marks for selected students.
     */

public function store(Request $request)
{
    Log::info('Store request received', $request->all());

    $request->validate([
        'class_id' => 'required|exists:school_classes,id',
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'subject_id' => 'required|exists:subjects,id',
        'exam_id' => 'required|exists:exams,id',
        'marks' => 'required|array',
        'marks.*' => 'numeric|min:0|max:100',
    ]);

    foreach ($request->marks as $student_id => $mark_value) {
        Log::info("Saving mark for student $student_id: $mark_value");
        $grade = Grade::gradeForMark($mark_value);

        try {
            Mark::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'subject_id' => $request->subject_id,
                    'exam_id' => $request->exam_id,
                    'academic_session_id' => $request->academic_session_id,
                ],
                [
                    'mark' => $mark_value,
                    'grade_id' => $grade ? $grade->id : null,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to save mark for student $student_id: " . $e->getMessage());
        }
    }

    return redirect()->route('marks.index')->with('success','Marks recorded successfully.');
}


    /**
     * Show form to edit a single mark.
     */
    public function edit(Mark $mark)
    {
        $students = Student::all();
        $subjects = Subject::all();
        $exams    = Exam::all();
        $grades   = Grade::all();
        $sessions = AcademicSession::all();
        $classes  = SchoolClass::all();

        return view('marks.edit', compact('mark','students','subjects','exams','grades','sessions','classes'));
    }

    /**
     * Update a single mark.
     */
    public function update(Request $request, Mark $mark)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'mark' => 'required|numeric|min:0|max:100',
        ]);

        $grade = Grade::gradeForMark($request->mark);

        $mark->update([
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'exam_id' => $request->exam_id,
            'academic_session_id' => $request->academic_session_id,
            'mark' => $request->mark,
            'grade_id' => $grade ? $grade->id : null,
        ]);

        return redirect()->route('marks.index')->with('success','Mark updated successfully.');
    }

    /**
     * Delete a mark.
     */
    public function destroy(Mark $mark)
    {
        $mark->delete();
        return redirect()->route('marks.index')->with('success','Mark deleted successfully.');
    }

    /**
     * Show GPA & Division for a student.
     */
    public function studentResult(Student $student)
    {
        $marks = $student->marks()->with('subject')->get()->mapWithKeys(function($m){
            return [$m->subject->name ?? 'N/A' => $m->mark];
        })->toArray();

        $result = StudentResultService::calculateGpaAndDivision($marks);

        return view('marks.result', compact('student','result'));
    }
}
