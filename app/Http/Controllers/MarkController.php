<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Mark;
use App\Models\MarkQuestionScore;
use App\Models\ExamQuestion;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\AcademicSession;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use App\Exports\QuestionMarksTemplateExport;
use App\Imports\QuestionMarksImport;
use Maatwebsite\Excel\Facades\Excel;

class MarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view marks')->only(['index', 'getSubjectsByDepartment']);
        $this->middleware('permission:enter marks')->only(['create', 'store', 'edit', 'update', 'getStudents']);
        $this->middleware('permission:delete marks')->only(['destroy']);
    }

    /**
     * Display a listing of marks with statistics, ranking, and grade distribution.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $departments = \App\Models\Department::all();

        // subject_class.teacher_id is a foreign key to staff.id, not users.id.
        $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;

        // Base subjects query
        $subjectsQuery = Subject::query();
        if ($user->hasRole('Teacher')) {
            $subjectsQuery->whereHas('classes', fn($q) => $q->where('teacher_id', $staffId));
        }
        if ($request->filled('department_id')) {
            $subjectsQuery->where('department_id', $request->department_id);
        }
        $subjects = $subjectsQuery->get();

        // Exams for filter dropdown (optionally filtered by session)
        $examsQuery = Exam::query();
        if ($request->filled('academic_session_id')) {
            $examsQuery->where('academic_session_id', $request->academic_session_id);
        }
        $exams = $examsQuery->orderBy('name')->get();

        // Marks query builder (with eager loading)
        $marksQuery = Mark::with(['student.schoolClass', 'subject.department', 'exam', 'grade']);

        // Apply filters – prefix ambiguous columns with 'marks.'
        if ($request->filled('academic_session_id')) {
            $marksQuery->where('marks.academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('class_id')) {
            $marksQuery->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }
        if ($request->filled('department_id')) {
            $marksQuery->whereHas('subject', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('subject_id')) {
            $marksQuery->where('marks.subject_id', $request->subject_id);
        }
        if ($request->filled('exam_id')) {
            $marksQuery->where('marks.exam_id', $request->exam_id);
        }
        if ($user->hasRole('Teacher')) {
            $marksQuery->whereHas('subject.classes', fn($q) => $q->where('teacher_id', $staffId));
        }

        // Statistics, ranking, and grade distribution (only when a specific exam, subject, class, and session are selected)
        $stats = null;
        $rankedMarks = null;

        if ($request->filled('academic_session_id') && $request->filled('class_id') && $request->filled('subject_id') && $request->filled('exam_id')) {
            // Clone the query to get all results for stats (without pagination)
            $fullMarks = clone $marksQuery;
            $allMarks = $fullMarks->get();

            // Basic stats
            $stats = [
                'total_students'  => $allMarks->count(),
                'average_mark'    => $allMarks->avg('mark'),
                'highest_mark'    => $allMarks->max('mark'),
                'lowest_mark'     => $allMarks->min('mark'),
            ];
            $stats['passing_count'] = $allMarks->filter(fn($m) => $m->mark >= 50)->count();
            $stats['passing_percentage'] = $stats['total_students'] ? round(($stats['passing_count'] / $stats['total_students']) * 100, 2) : 0;

            // Grade distribution – uses the `grades` table
            $gradeCounts = [];
            $grades = Grade::orderBy('min_mark', 'desc')->get();
            foreach ($grades as $grade) {
                $count = $allMarks->filter(fn($m) => $m->mark >= $grade->min_mark && $m->mark <= $grade->max_mark)->count();
                if ($count > 0 || $grade->name == 'F') {
                    $gradeCounts[$grade->name] = $count;
                }
            }
            $lowestGrade = $grades->last();
            if ($lowestGrade && $stats['total_students'] > 0) {
                $belowLowest = $allMarks->filter(fn($m) => $m->mark < $lowestGrade->min_mark)->count();
                if ($belowLowest > 0) {
                    $gradeCounts['F'] = ($gradeCounts['F'] ?? 0) + $belowLowest;
                }
            }
            $stats['grade_counts'] = $gradeCounts;

            // Compute global rank (descending by mark)
            $sorted = $allMarks->sortByDesc('mark')->values();
            $rank = 1;
            $prevMark = null;
            foreach ($sorted as $index => $mark) {
                if ($prevMark !== null && $mark->mark < $prevMark) {
                    $rank = $index + 1;
                }
                $mark->rank = $rank;
                $prevMark = $mark->mark;
            }
            $rankedMarks = $sorted->keyBy('id');
            
            // Add ranked_marks to stats for Top Performer in view
            $stats['ranked_marks'] = $sorted;
        }

        // Paginate the marks – sorted alphabetically by student name
        $marks = $marksQuery->join('students', 'marks.student_id', '=', 'students.id')
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->select('marks.*')
            ->paginate(20);

        // ✅ IMPORTANT: Load grade relationship manually after pagination to ensure it's available
        $marks->load('grade');

        // Attach ranks to the paginated collection
        if ($rankedMarks) {
            foreach ($marks as $mark) {
                if (isset($rankedMarks[$mark->id])) {
                    $mark->rank = $rankedMarks[$mark->id]->rank;
                }
            }
        }

        return view('marks.index', compact('marks', 'sessions', 'classes', 'departments', 'subjects', 'exams', 'stats'));
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

        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;
            // Only subjects assigned to this teacher via pivot
            $subjects = \App\Models\Subject::whereHas('classes', function($q) use ($staffId) {
                $q->where('teacher_id', $staffId);
            })->get();
        } else {
            $subjects = Subject::all();
        }

        // Retrieve old input from session (flashed after redirect)
        $oldInput = old();
        $selectedSession = $oldInput['academic_session_id'] ?? null;
        $selectedClass    = $oldInput['class_id'] ?? null;
        $selectedDepartment = $oldInput['department_id'] ?? null;
        $selectedSubject  = $oldInput['subject_id'] ?? null;
        $selectedExam     = $oldInput['exam_id'] ?? null;

        return view('marks.create', compact(
            'sessions', 'classes', 'subjects',
            'selectedSession', 'selectedClass', 'selectedDepartment',
            'selectedSubject', 'selectedExam'
        ));
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
        ]);

        // Convert empty strings to null and validate numeric values manually
        $marks = $request->input('marks', []);
        foreach ($marks as $student_id => $markValue) {
            if ($markValue !== null && $markValue !== '') {
                if (!is_numeric($markValue) || $markValue < 0 || $markValue > 100) {
                    return redirect()->back()
                        ->withErrors(['marks' => "Mark for student ID $student_id must be a number between 0 and 100."])
                        ->withInput();
                }
            }
        }

        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;
            $assigned = \App\Models\Subject::where('id', $request->subject_id)
                ->whereHas('classes', function ($q) use ($request, $staffId) {
                    $q->where('class_id', $request->class_id)
                      ->where('teacher_id', $staffId);
                })
                ->exists();

            if (!$assigned) {
                abort(403, "You are not assigned to this subject for the selected class.");
            }
        }

        try {
            foreach ($marks as $student_id => $markValue) {
                // Skip empty values (absent students)
                if ($markValue === null || $markValue === '') {
                    // Delete existing mark if it exists
                    Mark::where([
                        'student_id' => $student_id,
                        'subject_id' => $request->subject_id,
                        'exam_id' => $request->exam_id,
                        'academic_session_id' => $request->academic_session_id,
                    ])->delete();
                    continue;
                }

                $enrollment = Enrollment::where('student_id', $student_id)
                    ->where('class_id', $request->class_id)
                    ->where('academic_session_id', $request->academic_session_id)
                    ->where('status', 'active')
                    ->first();

                if (!$enrollment) continue;

                $student = $enrollment->student;

                // Check if withdrawn (skip if withdrawn)
                $withdrawn = $student->subjects()
                    ->where('subject_id', $request->subject_id)
                    ->wherePivot('withdrawn', 1)
                    ->exists();

                if ($withdrawn) continue;

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

            return redirect()->route('marks.index')
                ->with('success', 'Marks saved successfully with grades!')
                ->withInput($request->only([
                    'academic_session_id', 'class_id', 'department_id', 'subject_id', 'exam_id'
                ]));

        } catch (\Exception $e) {
            return redirect()->route('marks.create')
                ->with('error', 'Failed to save marks: ' . $e->getMessage())
                ->withInput($request->only([
                    'academic_session_id', 'class_id', 'department_id', 'subject_id', 'exam_id'
                ]));
        }
    }

    /**
     * AJAX: Get students of a class, session, and subject (enrolled only, exclude withdrawn)
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id'    => 'required|exists:exams,id',
        ]);

        // Get all active enrollments for the selected class and session
        $enrollments = Enrollment::with('student')
            ->where('class_id', $request->class_id)
            ->where('academic_session_id', $request->session_id)
            ->where('status', 'active')
            ->get();

        $students = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;

            // Check if the student is explicitly withdrawn from this subject
            $isWithdrawn = $student->subjects()
                ->where('subject_id', $request->subject_id)
                ->wherePivot('withdrawn', 1)
                ->exists();

            // Skip only withdrawn students – all others are included
            if ($isWithdrawn) {
                continue;
            }

            // Fetch any existing mark for this student, subject, session, and exam.
            $existingMark = Mark::where('student_id', $student->id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_session_id', $request->session_id)
                ->where('exam_id', $request->exam_id)
                ->first();

            $students[] = [
                'id'         => $student->id,
                'first_name' => $student->first_name,
                'last_name'  => $student->last_name,
                'mark'       => $existingMark ? $existingMark->mark : null,
            ];
        }

        // Sort alphabetically by full name
        usort($students, function ($a, $b) {
            $nameA = $a['first_name'] . ' ' . $a['last_name'];
            $nameB = $b['first_name'] . ' ' . $b['last_name'];
            return strcasecmp($nameA, $nameB);
        });

        return response()->json($students);
    }

    /**
     * AJAX: Get subjects by department (for dynamic dropdown)
     */
    public function getSubjectsByDepartment(Request $request)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        // subject_class.teacher_id is a foreign key to staff.id, not users.id.
        $staffId = $user->hasRole('Teacher')
            ? optional(\App\Models\Staff::where('user_id', $user->id)->first())->id
            : null;

        $query = Subject::query()
            ->with(['classes' => function ($q) use ($user, $staffId) {
                if ($user->hasRole('Teacher')) {
                    $q->where('teacher_id', $staffId);
                }
            }]);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // If teacher, only return subjects assigned to them via pivot
        if ($user->hasRole('Teacher')) {
            $query->whereHas('classes', function($q) use ($staffId) {
                $q->where('teacher_id', $staffId);
            });
        }

        $subjects = $query->get(['id', 'name']);

        return response()->json($subjects);
    }

   /**
 * AJAX: Get exams by academic session (for dynamic dropdown)
 * Optional: filter by exam_type = 'terminal', 'annual', or 'both'
 */
public function getExamsBySession(Request $request)
{
    $request->validate([
        'session_id' => 'required|exists:academic_sessions,id',
    ]);

    $sessionId = $request->session_id;
    $examType  = $request->query('exam_type'); // 'terminal', 'annual', 'both'

    $query = Exam::where('academic_session_id', $sessionId);

    if ($examType === 'terminal') {
        $query->where('is_terminal_exam', 1);
    } elseif ($examType === 'annual') {
        $query->where('is_annual_exam', 1);
    } elseif ($examType === 'both') {
        $query->where(function ($q) {
            $q->where('is_terminal_exam', 1)
              ->orWhere('is_annual_exam', 1);
        });
    }
    // If no exam_type or any other value, return all exams (backward compatible)

    $exams = $query->orderBy('name', 'asc')
                   ->get(['id', 'name']);

    return response()->json($exams);
}

    /**
     * AJAX: Get grade name and points for a given mark (for preview in edit form)
     */
    public function getGrade(Request $request)
    {
        $request->validate([
            'mark' => 'required|numeric|min:0|max:100',
        ]);
        
        $grade = Grade::where('min_mark', '<=', $request->mark)
            ->where('max_mark', '>=', $request->mark)
            ->first();
        
        return response()->json([
            'grade' => $grade->name ?? 'N/A',
            'points' => $grade->point ?? 0,
        ]);
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

        // Teacher: only subjects assigned to them
        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;
            $subjects = Subject::whereHas('classes', function($q) use ($staffId) {
                $q->where('teacher_id', $staffId);
            })->get();
            
            // Also ensure the current mark's subject is included even if not assigned (view only)
            $currentSubject = Subject::find($mark->subject_id);
            if ($currentSubject && !$subjects->contains('id', $currentSubject->id)) {
                $subjects->push($currentSubject);
            }
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
            // Get the class_id from the mark
            $classId = $mark->class_id;
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;

            $isAssigned = Subject::where('id', $request->subject_id)
                ->whereHas('classes', function($q) use ($classId, $staffId) {
                    $q->where('class_id', $classId)
                      ->where('teacher_id', $staffId);
                })
                ->exists();
            
            // If not assigned and it's not the original subject, deny
            if (!$isAssigned && $mark->subject_id != $request->subject_id) {
                abort(403, "You are not assigned to this subject.");
            }
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

    // ─────────────────────────────────────────────────────────────────────────
    // Question-based mark entry
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * AJAX: Return enrolled students with their existing question scores for
     * the selected class / session / subject / exam combination.
     */
    public function getStudentsWithQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id'    => 'required|exists:exams,id',
        ]);

        $questions = ExamQuestion::where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->orderBy('question_no')
            ->get();

        if ($questions->isEmpty()) {
            return response()->json(['has_questions' => false, 'students' => [], 'questions' => []]);
        }

        $enrollments = Enrollment::with('student')
            ->where('class_id', $request->class_id)
            ->where('academic_session_id', $request->session_id)
            ->where('status', 'active')
            ->get();

        $students = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $isWithdrawn = $student->subjects()
                ->where('subject_id', $request->subject_id)
                ->wherePivot('withdrawn', 1)
                ->exists();
            if ($isWithdrawn) continue;

            // Existing mark → existing question scores
            $mark = Mark::where('student_id', $student->id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_session_id', $request->session_id)
                ->where('exam_id', $request->exam_id)
                ->with('questionScores')
                ->first();

            $scores = [];
            if ($mark) {
                foreach ($mark->questionScores as $qs) {
                    $scores[$qs->exam_question_id] = (float) $qs->score;
                }
            }

            $students[] = [
                'id'         => $student->id,
                'first_name' => $student->first_name,
                'last_name'  => $student->last_name,
                'scores'     => $scores, // keyed by exam_question_id
            ];
        }

        usort($students, fn($a, $b) => strcasecmp("{$a['first_name']} {$a['last_name']}", "{$b['first_name']} {$b['last_name']}"));

        return response()->json([
            'has_questions' => true,
            'questions'     => $questions->map(fn($q) => [
                'id'          => $q->id,
                'question_no' => $q->question_no,
                'description' => $q->description,
                'max_marks'   => (float) $q->max_marks,
            ]),
            'students'      => $students,
        ]);
    }

    /**
     * Store marks recorded question-by-question.
     * Computes percentage total and saves it to marks.mark (keeps grading intact).
     */
    public function storeByQuestions(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'class_id'            => 'required|exists:school_classes,id',
            'subject_id'          => 'required|exists:subjects,id',
            'exam_id'             => 'required|exists:exams,id',
            'scores'              => 'required|array',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;
            $assigned = Subject::where('id', $request->subject_id)
                ->whereHas('classes', fn($q) => $q->where('class_id', $request->class_id)->where('teacher_id', $staffId))
                ->exists();
            if (!$assigned) {
                abort(403, 'You are not assigned to this subject for the selected class.');
            }
        }

        $questions = ExamQuestion::where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->orderBy('question_no')
            ->get()
            ->keyBy('id');

        if ($questions->isEmpty()) {
            return redirect()->back()->with('error', 'No questions defined for this exam and subject.');
        }

        $totalMax = $questions->sum('max_marks');

        DB::transaction(function () use ($request, $questions, $totalMax, $user) {
            foreach ($request->scores as $studentId => $questionScores) {
                if (!is_array($questionScores) || empty(array_filter($questionScores, fn($v) => $v !== null && $v !== ''))) {
                    // No scores entered — remove existing mark
                    Mark::where([
                        'student_id'          => $studentId,
                        'subject_id'          => $request->subject_id,
                        'exam_id'             => $request->exam_id,
                        'academic_session_id' => $request->academic_session_id,
                    ])->delete();
                    continue;
                }

                $enrollment = Enrollment::where('student_id', $studentId)
                    ->where('class_id', $request->class_id)
                    ->where('academic_session_id', $request->academic_session_id)
                    ->where('status', 'active')
                    ->first();
                if (!$enrollment) continue;

                // Sum raw scores; cap each at the question's max
                $rawTotal = 0;
                $scoresToSave = [];
                foreach ($questions as $qId => $question) {
                    $val = $questionScores[$qId] ?? null;
                    if ($val === null || $val === '') {
                        $val = 0;
                    }
                    $val = min((float) $val, (float) $question->max_marks);
                    $rawTotal += $val;
                    $scoresToSave[$qId] = $val;
                }

                // Convert to percentage (0-100) for the mark column
                $percentage = $totalMax > 0 ? round(($rawTotal / $totalMax) * 100, 2) : 0;

                $grade = Grade::where('min_mark', '<=', $percentage)
                    ->where('max_mark', '>=', $percentage)
                    ->first();

                $mark = Mark::updateOrCreate(
                    [
                        'student_id'          => $studentId,
                        'subject_id'          => $request->subject_id,
                        'exam_id'             => $request->exam_id,
                        'academic_session_id' => $request->academic_session_id,
                    ],
                    [
                        'mark'     => $percentage,
                        'grade_id' => $grade?->id,
                        'class_id' => $request->class_id,
                    ]
                );

                // Save per-question scores
                foreach ($scoresToSave as $qId => $score) {
                    MarkQuestionScore::updateOrCreate(
                        ['mark_id' => $mark->id, 'exam_question_id' => $qId],
                        ['score'   => $score]
                    );
                }
            }
        });

        return redirect()->route('marks.index')
            ->with('success', 'Marks saved by questions successfully!')
            ->withInput($request->only(['academic_session_id', 'class_id', 'department_id', 'subject_id', 'exam_id']));
    }

    /**
     * Download a pre-filled Excel template for By Questions mark entry.
     */
    public function downloadQuestionMarksTemplate(Request $request)
    {
        $request->validate([
            'class_id'            => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'exam_id'             => 'required|exists:exams,id',
            'subject_id'          => 'required|exists:subjects,id',
        ]);

        $exam    = Exam::find($request->exam_id);
        $subject = Subject::find($request->subject_id);
        $filename = 'question_marks_' . str($exam->name)->slug() . '_' . str($subject->name)->slug() . '.xlsx';

        return Excel::download(
            new QuestionMarksTemplateExport(
                (int) $request->class_id,
                (int) $request->academic_session_id,
                (int) $request->exam_id,
                (int) $request->subject_id,
            ),
            $filename
        );
    }

    /**
     * Import By Questions marks from an uploaded Excel file.
     */
    public function importQuestionMarks(Request $request)
    {
        $request->validate([
            'question_excel_file' => 'required|file|mimes:xlsx,xls',
            'class_id'            => 'required|exists:school_classes,id',
            'session_id'          => 'required|exists:academic_sessions,id',
            'subject_id'          => 'required|exists:subjects,id',
            'exam_id'             => 'required|exists:exams,id',
        ]);

        try {
            $import = new QuestionMarksImport(
                (int) $request->class_id,
                (int) $request->session_id,
                (int) $request->exam_id,
                (int) $request->subject_id,
            );
            Excel::import($import, $request->file('question_excel_file'));

            $count  = $import->getSuccessCount();
            $errors = $import->getErrors();

            if ($count === 0 && count($errors) > 0) {
                return back()->with('error', 'Import failed: ' . implode(', ', array_slice($errors, 0, 3)));
            }
            if (count($errors) > 0) {
                return back()->with('warning', "Imported $count student(s). Issues: " . implode(', ', array_slice($errors, 0, 3)));
            }
            return back()->with('success', "Successfully imported question marks for $count student(s).");
        } catch (\Exception $e) {
            Log::error('[Question Marks Import] ' . $e->getMessage());
            return back()->with('error', 'System error during import: ' . $e->getMessage());
        }
    }

    /**
     * Question evaluation report — shows per-question class performance.
     */
    public function questionEvaluation(Request $request)
    {
        $sessions    = AcademicSession::orderBy('name')->get();
        $classes     = SchoolClass::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        $subjects    = Subject::orderBy('name')->get();

        // Exams filtered by selected session (or all if none selected)
        $examsQuery = Exam::orderBy('name');
        if ($request->filled('session_id')) {
            $examsQuery->where('academic_session_id', $request->session_id);
        }
        $exams = $examsQuery->get();

        $report     = null;
        $questions  = collect();
        $studentRows = collect();

        if ($request->filled('exam_id') && $request->filled('subject_id')) {
            $questions = ExamQuestion::where('exam_id', $request->exam_id)
                ->where('subject_id', $request->subject_id)
                ->orderBy('question_no')
                ->get();

            if ($questions->isNotEmpty()) {
                // Fetch all marks for this exam+subject (+optional class filter)
                $marksQuery = Mark::where('exam_id', $request->exam_id)
                    ->where('subject_id', $request->subject_id)
                    ->with(['student.schoolClass', 'questionScores.examQuestion']);

                if ($request->filled('class_id')) {
                    $marksQuery->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
                }
                if ($request->filled('session_id')) {
                    $marksQuery->where('academic_session_id', $request->session_id);
                }

                $marks = $marksQuery->get();

                // Build per-student rows
                $studentRows = $marks->map(function ($mark) use ($questions) {
                    $scores = $mark->questionScores->keyBy('exam_question_id');
                    $row = [
                        'student'    => $mark->student,
                        'mark'       => (float) $mark->mark,
                        'grade'      => $mark->grade,
                        'q_scores'   => [],
                        'raw_total'  => 0,
                    ];
                    foreach ($questions as $q) {
                        $score = $scores[$q->id]->score ?? null;
                        $row['q_scores'][$q->id] = $score !== null ? (float) $score : null;
                        $row['raw_total'] += (float) ($score ?? 0);
                    }
                    return $row;
                })->sortBy(fn($r) => $r['student']->first_name . ' ' . $r['student']->last_name)->values();

                // Per-question stats
                $totalMax = $questions->sum('max_marks');
                $questionStats = $questions->map(function ($q) use ($studentRows) {
                    $scores = $studentRows->pluck("q_scores.{$q->id}")->filter(fn($s) => $s !== null);
                    $count  = $scores->count();
                    $avg    = $count ? round($scores->avg(), 2) : null;
                    $pct    = ($avg !== null && $q->max_marks > 0) ? round(($avg / $q->max_marks) * 100, 1) : null;
                    return [
                        'question'  => $q,
                        'avg_score' => $avg,
                        'avg_pct'   => $pct,
                        'count'     => $count,
                        'mastered'  => $scores->filter(fn($s) => $q->max_marks > 0 && ($s / $q->max_marks) >= 0.7)->count(),
                        'struggling'=> $scores->filter(fn($s) => $q->max_marks > 0 && ($s / $q->max_marks) < 0.5)->count(),
                        'max_marks' => (float) $q->max_marks,
                    ];
                });

                $report = [
                    'exam'          => Exam::find($request->exam_id),
                    'subject'       => Subject::find($request->subject_id),
                    'class'         => $request->filled('class_id') ? SchoolClass::find($request->class_id) : null,
                    'total_max'     => $totalMax,
                    'student_count' => $studentRows->count(),
                    'question_stats'=> $questionStats,
                    'class_avg_pct' => $studentRows->isNotEmpty() ? round($studentRows->avg('mark'), 1) : null,
                ];
            }
        }

        return view('marks.question_evaluation', compact(
            'sessions', 'classes', 'departments', 'exams', 'subjects',
            'questions', 'studentRows', 'report'
        ));
    }
}