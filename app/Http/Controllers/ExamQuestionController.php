<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['Admin', 'HOD'])) {
                abort(403, 'Only Admins and HODs can manage exam questions.');
            }
            return $next($request);
        });
    }

    /**
     * Show the question setup page.
     */
    public function manage()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $sessions    = \App\Models\AcademicSession::orderBy('name')->get();
        $classes     = \App\Models\SchoolClass::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        $exams       = Exam::orderBy('name')->get();

        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id.
            // (Dead branch in practice — the constructor above already
            // restricts this whole controller to Admin/HOD.)
            $staffId = optional(\App\Models\Staff::where('user_id', $user->id)->first())->id;
            $subjects = Subject::whereHas('classes', fn($q) => $q->where('teacher_id', $staffId))
                ->orderBy('name')->get();
        } else {
            $subjects = Subject::orderBy('name')->get();
        }

        return view('exam_questions.manage', compact('sessions', 'classes', 'departments', 'exams', 'subjects'));
    }

    /**
     * AJAX: Return questions for a given exam + subject.
     */
    public function getQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $questions = ExamQuestion::where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->orderBy('question_no')
            ->get(['id', 'question_no', 'description', 'max_marks']);

        return response()->json($questions);
    }

    /**
     * Save (replace) all questions for an exam + subject.
     * Deletes removed questions and upserts the rest.
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id'                  => 'required|exists:exams,id',
            'subject_id'               => 'required|exists:subjects,id',
            'questions'                => 'required|array|min:1',
            'questions.*.max_marks'    => 'required|numeric|min:0.5|max:999',
            'questions.*.description'  => 'nullable|string|max:200',
        ]);

        $examId    = $request->exam_id;
        $subjectId = $request->subject_id;

        // Delete all existing questions (cascades to mark_question_scores)
        ExamQuestion::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->delete();

        // Re-insert in order
        foreach ($request->questions as $index => $q) {
            ExamQuestion::create([
                'exam_id'     => $examId,
                'subject_id'  => $subjectId,
                'question_no' => $index + 1,
                'description' => $q['description'] ?? null,
                'max_marks'   => $q['max_marks'],
            ]);
        }

        $total = collect($request->questions)->sum('max_marks');

        return response()->json([
            'success' => true,
            'message' => count($request->questions) . ' questions saved. Total marks: ' . $total,
        ]);
    }
}
