<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\StudentResult;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StudentResultService;

class StudentResultController extends Controller
{
    /**
     * Display paginated list of students.
     */
    public function index()
    {
        $students = Student::paginate(10);
        return view('results.index', compact('students'));
    }


    public function classResults(Request $request)
{
    // Fetch all classes and exams for dropdowns
    $classes = \App\Models\SchoolClass::all();
    $exams = \App\Models\Exam::all();

    $selectedClassId = $request->class_id;
    $selectedExamId = $request->exam_id;

    $studentsData = [];

    if ($selectedClassId && $selectedExamId) {
        $students = \App\Models\Student::where('class_id', $selectedClassId)->get();
        $grades = \App\Models\Grade::all();

        foreach ($students as $student) {
            $marks = $student->marks()->where('exam_id', $selectedExamId)->with('subject')->get();

            $subjectsData = [];
            foreach ($marks as $mark) {
                $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

                $subjectsData[] = [
                    'subject' => $mark->subject->name ?? '-',
                    'mark' => $mark->mark,
                    'grade' => $grade->name ?? '-',
                    'point' => $grade->point ?? 0,
                ];
            }

            // Calculate GPA for student
            $bestMarks = collect($subjectsData)->sortByDesc('mark')->take(7)->pluck('mark')->toArray();
            $gpaResult = \App\Services\StudentResultService::calculateGpaAndDivision($bestMarks);

            $studentsData[] = [
                'student' => $student,
                'total_points' => collect($subjectsData)->sum('point'),
                'gpa' => $gpaResult['gpa'],
                'division' => $gpaResult['division'],
            ];
        }

        // Sort by total points to calculate class position
        usort($studentsData, fn($a, $b) => $b['total_points'] <=> $a['total_points']);
        foreach ($studentsData as $i => &$data) {
            $data['position'] = $i + 1;
        }
    }

    return view('results.class_results', compact(
        'classes', 'exams', 'studentsData', 'selectedClassId', 'selectedExamId'
    ));
}


    /**
     * Display detailed results for a specific student by exam.
     */
    public function show(Student $student, Request $request)
    {
        try {
            $exams = Exam::all();
            $selectedExam = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;

            $grades = Grade::all();

            // Fetch marks for selected exam
            $marksQuery = $student->marks()->with('subject');
            if ($selectedExam) {
                $marksQuery->where('exam_id', $selectedExam->id);
            }
            $marks = $marksQuery->get();

            if ($marks->isEmpty()) {
                return back()->with('warning', 'No marks found for this student in the selected exam.');
            }

            // --- Build subject data ---
            $subjectsData = $marks->map(function ($mark) use ($grades, $student, $selectedExam) {
                $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

                // Calculate subject position
                $subjectMarks = Mark::where('subject_id', $mark->subject_id)
                    ->where('exam_id', $selectedExam->id ?? 0)
                    ->orderByDesc('mark')
                    ->pluck('student_id')
                    ->toArray();
                $subjectPosition = array_search($student->id, $subjectMarks) !== false
                    ? array_search($student->id, $subjectMarks) + 1
                    : '-';

                return [
                    'subject' => $mark->subject->name ?? 'Unknown',
                    'type' => $mark->subject->type ?? 'core',
                    'mark' => $mark->mark,
                    'grade' => $grade->name ?? '-',
                    'point' => $grade->point ?? 0,
                    'remark' => $grade->description ?? '',
                    'subject_position' => $subjectPosition,
                ];
            });

            // --- Best 7 subjects for GPA calculation ---
            $coreSubjects = $subjectsData->where('type', 'core')->sortByDesc('mark');
            $electives = $subjectsData->where('type', 'elective')->sortByDesc('mark');

            $bestSubjects = $coreSubjects->take(7);
            if ($bestSubjects->count() < 7) {
                $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
            }

            $bestMarks = $bestSubjects->pluck('mark')->toArray();
            $result = StudentResultService::calculateGpaAndDivision($bestMarks);
            $totalPoints = $bestSubjects->sum('point');

            // --- Save/update result ---
            if ($selectedExam) {
                StudentResult::updateOrCreate(
                    ['student_id' => $student->id, 'exam_id' => $selectedExam->id],
                    ['gpa' => $result['gpa'], 'total_points' => $totalPoints, 'division' => $result['division']]
                );
            }

            // --- Calculate class rank ---
            $rank = '-';
            if ($selectedExam) {
                $classStudents = Student::where('class_id', $student->class_id)->get();
                $positions = [];

                foreach ($classStudents as $s) {
                    $sMarks = $s->marks()->with('subject')->where('exam_id', $selectedExam->id)->get();
                    if ($sMarks->isEmpty()) continue;

                    $sSubjectsData = $sMarks->map(function ($m) use ($grades) {
                        $g = $grades->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark);
                        return [
                            'mark' => $m->mark,
                            'point' => $g->point ?? 0,
                            'type' => $m->subject->type ?? 'core',
                        ];
                    });

                    $core = $sSubjectsData->where('type', 'core')->sortByDesc('mark');
                    $electives = $sSubjectsData->where('type', 'elective')->sortByDesc('mark');
                    $best = $core->take(7);
                    if ($best->count() < 7) $best = $best->merge($electives->take(7 - $best->count()));

                    $positions[$s->id] = $best->sum('point');
                }

                arsort($positions);
                $rankPosition = array_search($student->id, array_keys($positions));
                $totalStudents = count($positions);
                $rank = $rankPosition !== false ? ($rankPosition + 1) . '/' . $totalStudents : '-';
            }

            // --- GPA Trend ---
            $gpaTrend = $exams->map(function ($exam) use ($student, $grades) {
                $examMarks = $student->marks()->with('subject')->where('exam_id', $exam->id)->get();
                if ($examMarks->isEmpty()) return null;

                $examSubjectsData = $examMarks->map(function ($m) use ($grades) {
                    $g = $grades->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark);
                    return [
                        'mark' => $m->mark,
                        'point' => $g->point ?? 0,
                        'type' => $m->subject->type ?? 'core',
                    ];
                });

                $core = $examSubjectsData->where('type', 'core')->sortByDesc('mark');
                $electives = $examSubjectsData->where('type', 'elective')->sortByDesc('mark');
                $best = $core->take(7);
                if ($best->count() < 7) $best = $best->merge($electives->take(7 - $core->count()));

                return [
                    'exam' => $exam->name,
                    'gpa' => StudentResultService::calculateGpaAndDivision($best->pluck('mark')->toArray())['gpa']
                ];
            })->filter(); // remove nulls

            // --- Subject Trend ---
            $subjects = Subject::all();
            $subjectTrend = [];
            foreach ($subjects as $subject) {
                $marksByExam = $exams->map(function ($exam) use ($student, $subject) {
                    $mark = Mark::where([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'exam_id' => $exam->id,
                    ])->value('mark') ?? 0;

                    return ['exam' => $exam->name, 'mark' => $mark];
                });
                $subjectTrend[$subject->name] = $marksByExam;
            }

            return view('results.show', [
                'student' => $student,
                'exam' => $selectedExam,
                'exams' => $exams,
                'subjectsData' => $subjectsData,
                'result' => $result,
                'totalPoints' => $totalPoints,
                'selected_exam_id' => $selectedExam->id ?? null,
                'rank' => $rank,
                'gpaTrend' => $gpaTrend,
                'subjectTrend' => $subjectTrend,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error calculating student results: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while calculating results.');
        }
    }
}
