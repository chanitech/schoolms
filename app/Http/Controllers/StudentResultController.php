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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClassResultsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolClass;
use App\Models\Division;
use App\Models\SchoolInfo;

class StudentResultController extends Controller
{
    public function __construct()
    {
        // Spatie permissions
        $this->middleware('permission:view results')->only(['index', 'show', 'classResults']);
        $this->middleware('permission:export results')->only([
            'exportExcel', 'exportPDF',
            'exportExcelNoticeBoard', 'exportPDFNoticeBoard'
        ]);
    }

    /**
     * Display paginated list of students.
     */
    public function index(Request $request)
{
    $this->authorize('view results');

    // Fetch all classes and sessions for filter dropdowns
    $classes = \App\Models\SchoolClass::all();
    $sessions = \App\Models\AcademicSession::all();

    // Start query
    $query = \App\Models\Student::with(['class', 'academicSession']);

    // Apply class filter
    if ($request->filled('class_id')) {
        $query->where('class_id', $request->class_id);
    }

    // Apply session filter
    if ($request->filled('session_id')) {
        $query->where('academic_session_id', $request->session_id);
    }

    // Paginate results
    $students = $query->paginate(10)->withQueryString();

    return view('results.index', compact('students', 'classes', 'sessions'));
}




    /**
     * Display class results with filters.
     */
    public function classResults(Request $request)
    {
        $this->authorize('view results');

        $classes = \App\Models\SchoolClass::all();
        $exams = Exam::all();

        $selectedClassId = $request->class_id;
        $selectedExamId = $request->exam_id;

        $studentsData = $this->getClassResultsDataWithSubjects($request);

        $subjects = Subject::all();

        return view('results.class_results', compact(
            'classes', 
            'exams', 
            'studentsData', 
            'selectedClassId', 
            'selectedExamId',
            'subjects'
        ));
    }

    /**
     * Show detailed student result.
     */
  public function show(Student $student, Request $request)
{
    $this->authorize('view results');

    try {
        $exams = Exam::all();
        $selectedExam = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;
        $grades = Grade::all();

        $marksQuery = $student->marks()->with('subject');
        if ($selectedExam) $marksQuery->where('exam_id', $selectedExam->id);
        $marks = $marksQuery->get();

        if ($marks->isEmpty()) {
            return back()->with('warning', 'No marks found for this student in the selected exam.');
        }

        // Prepare subjects data
        $subjectsData = $marks->map(function ($mark) use ($grades, $student, $selectedExam) {
            $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

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

        // NECTA Best 7: Core first, then electives
        $coreSubjects = $subjectsData->where('type', 'core')->sortByDesc('mark');
        $electives = $subjectsData->where('type', 'elective')->sortByDesc('mark');

        $bestSubjects = $coreSubjects->take(7);
        if ($bestSubjects->count() < 7) {
            $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
        }

        $bestMarks = $bestSubjects->pluck('mark')->toArray();
        $totalPoints = $bestSubjects->sum('point');
        $result = StudentResultService::calculateGpaAndDivision($bestMarks);

        if ($selectedExam) {
            StudentResult::updateOrCreate(
                ['student_id' => $student->id, 'exam_id' => $selectedExam->id],
                ['gpa' => $result['gpa'], 'total_points' => $totalPoints, 'division' => $result['division']]
            );
        }

        // Class rank calculation (Best 7 logic applied for all students)
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

                $sCore = $sSubjectsData->where('type', 'core')->sortByDesc('mark');
                $sElectives = $sSubjectsData->where('type', 'elective')->sortByDesc('mark');

                $sBest = $sCore->take(7);
                if ($sBest->count() < 7) {
                    $sBest = $sBest->merge($sElectives->take(7 - $sBest->count()));
                }

                $positions[$s->id] = $sBest->sum('point'); // total points from best 7
            }

            // Sort descending (highest points first)
            asort($positions);

            $rankPosition = array_search($student->id, array_keys($positions));
            $totalStudents = count($positions);
            $rank = $rankPosition !== false ? ($rankPosition + 1) . '/' . $totalStudents : '-';
        }

        // GPA Trend
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
            if ($best->count() < 7) {
                $best = $best->merge($electives->take(7 - $best->count()));
            }

            return [
                'exam' => $exam->name,
                'gpa' => StudentResultService::calculateGpaAndDivision($best->pluck('mark')->toArray())['gpa']
            ];
        })->filter();

        // Subject Trend
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




    /**
     * Export Excel for regular class results.
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('export results');

        $studentsData = $this->getClassResultsData($request);
        return Excel::download(new ClassResultsExport($studentsData), 'class_results.xlsx');
    }

    /**
     * Export PDF for regular class results.
     */
    

public function exportPDF(Request $request)
{
    $classId = $request->input('class_id');
    $examId = $request->input('exam_id');

    // Fetch necessary data
    $students = Student::where('class_id', $classId)->get();
    $subjects = Subject::all();
    $grades = Grade::all();
    $school = SchoolInfo::first(); // school info

    $studentsData = [];

    foreach ($students as $student) {
        $marks = Mark::where('student_id', $student->id)
                     ->where('exam_id', $examId)
                     ->with('subject')
                     ->get();

        $subjectsData = [];
        foreach ($marks as $mark) {
            $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);
            $subjectsData[] = [
                'subject' => $mark->subject->name ?? '-',
                'mark' => $mark->mark,
                'grade' => $grade->name ?? '-',
                'point' => $grade->point ?? 0,
                'type' => $mark->subject->type ?? 'core',
            ];
        }

        // NECTA Best 7: Core first, then electives
        $coreSubjects = collect($subjectsData)->where('type', 'core')->sortByDesc('mark');
        $electiveSubjects = collect($subjectsData)->where('type', 'elective')->sortByDesc('mark');

        $bestSubjects = $coreSubjects->take(7);
        if ($bestSubjects->count() < 7) {
            $bestSubjects = $bestSubjects->merge($electiveSubjects->take(7 - $bestSubjects->count()));
        }

        $totalPoints = $bestSubjects->sum('point');
        $bestMarks = $bestSubjects->pluck('mark')->toArray();
        $gpaResult = StudentResultService::calculateGpaAndDivision($bestMarks);

        $studentsData[] = [
            'student' => $student,
            'subjectsData' => $subjectsData,
            'bestSubjects' => $bestSubjects,
            'total_points' => $totalPoints,
            'gpa' => $gpaResult['gpa'],
            'division' => $gpaResult['division'],
        ];
    }

    // Sort by GPA then total points
    usort($studentsData, function ($a, $b) {
        if ($a['gpa'] === $b['gpa']) {
            return $b['total_points'] <=> $a['total_points'];
        }
        return $b['gpa'] <=> $a['gpa'];
    });

    foreach ($studentsData as $i => &$data) {
        $data['position'] = $i + 1;
    }

    // Generate PDF
    $pdf = Pdf::loadView('results.class_results_pdf', [
        'studentsData' => $studentsData,
        'subjects' => $subjects,
        'grades' => $grades,
        'school' => $school, // pass school info
    ])
    ->setPaper('A3', 'landscape')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    // Add watermark
    $pdf->getDomPDF()->getCanvas()->page_text(
        520, 800,
        $school->name ?? 'SCHOOL NAME',
        'Arial', 50, [0.85, 0.85, 0.85], 0.5
    );

    return $pdf->download('class_results.pdf');
}













    /**
     * Export Excel for notice board (all subjects).
     */
    public function exportExcelNoticeBoard(Request $request)
    {
        $this->authorize('export results');

        $data = $this->getClassResultsForNoticeBoard($request);
        return Excel::download(
            new ClassResultsExport($data['studentsData'], $data['subjects']),
            'class_results_notice_board.xlsx'
        );
    }

    /**
     * Export PDF for notice board (all subjects).
     */
    public function exportPDFNoticeBoard(Request $request)
    {
        $this->authorize('export results');

        $data = $this->getClassResultsForNoticeBoard($request);

        $pdf = Pdf::loadView('results.class_results_notice_board_pdf', [
            'studentsData' => $data['studentsData'],
            'subjects' => $data['subjects']
        ]);

        return $pdf->download('class_results_notice_board.pdf');
    }

    // --------------------- PRIVATE HELPERS --------------------- //

    private function getClassResultsData(Request $request)
    {
        $selectedClassId = $request->class_id;
        $selectedExamId = $request->exam_id;
        $studentsData = [];

        if ($selectedClassId && $selectedExamId) {
            $students = Student::where('class_id', $selectedClassId)->get();
            $grades = Grade::all();

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

                $bestMarks = collect($subjectsData)->sortByDesc('mark')->take(7)->pluck('mark')->toArray();
                $gpaResult = StudentResultService::calculateGpaAndDivision($bestMarks);

                $studentsData[] = [
                    'student' => $student,
                    'total_points' => collect($subjectsData)->sum('point'),
                    'gpa' => $gpaResult['gpa'],
                    'division' => $gpaResult['division'],
                ];
            }

            usort($studentsData, fn($a, $b) => $b['total_points'] <=> $a['total_points']);
            foreach ($studentsData as $i => &$data) {
                $data['position'] = $i + 1;
            }
        }

        return $studentsData;
    }

    private function getClassResultsForNoticeBoard(Request $request)
    {
        $selectedClassId = $request->class_id;
        $selectedExamId = $request->exam_id;

        $students = Student::where('class_id', $selectedClassId)->get();
        $grades = Grade::all();

        $subjectIds = Mark::where('exam_id', $selectedExamId)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->get();

        $studentsData = [];

        foreach ($students as $student) {
            $marks = $student->marks()->where('exam_id', $selectedExamId)->get()->keyBy('subject_id');

            $subjectsData = [];
            foreach ($subjects as $subject) {
                $mark = $marks[$subject->id]->mark ?? null;
                $grade = $grades->firstWhere(fn($g) => $mark !== null && $mark >= $g->min_mark && $mark <= $g->max_mark);

                $subjectsData[$subject->name] = [
                    'mark' => $mark,
                    'grade' => $grade->name ?? '-',
                    'point' => $grade->point ?? 0,
                ];
            }

            $bestMarks = collect($subjectsData)->pluck('mark')->filter()->sortDesc()->take(7)->toArray();
            $gpaResult = StudentResultService::calculateGpaAndDivision($bestMarks);
            $totalPoints = collect($subjectsData)->sum('point');

            $studentsData[] = [
                'student' => $student,
                'subjectsData' => $subjectsData,
                'total_points' => $totalPoints,
                'gpa' => $gpaResult['gpa'],
                'division' => $gpaResult['division'],
            ];
        }

        usort($studentsData, fn($a, $b) => $b['total_points'] <=> $a['total_points']);
        foreach ($studentsData as $i => &$data) {
            $data['position'] = $i + 1;
        }

        return ['studentsData' => $studentsData, 'subjects' => $subjects];
    }

    private function getClassResultsDataWithSubjects(Request $request)
{
    $selectedClassId = $request->class_id;
    $selectedExamId = $request->exam_id;
    $studentsData = [];

    if ($selectedClassId && $selectedExamId) {
        $students = Student::where('class_id', $selectedClassId)->get();
        $grades = Grade::all(); // NECTA grades with points
        $subjects = Subject::all();

        foreach ($students as $student) {
            $marks = $student->marks()->where('exam_id', $selectedExamId)->with('subject')->get();
            $subjectsData = [];

            foreach ($marks as $mark) {
                $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

                $subjectsData[] = [
                    'subject_id' => $mark->subject->id,
                    'name' => $mark->subject->name,
                    'type' => $mark->subject->type ?? 'core', // core or elective
                    'mark' => $mark->mark,
                    'point' => $grade->point ?? 0,
                    'grade' => $grade->name ?? '-',
                ];
            }

            // --- NECTA Best 7 calculation ---
            $coreSubjects = collect($subjectsData)->where('type', 'core')->sortByDesc('mark');
            $electiveSubjects = collect($subjectsData)->where('type', 'elective')->sortByDesc('mark');

            $bestSubjects = $coreSubjects->take(7);
            if ($bestSubjects->count() < 7) {
                $bestSubjects = $bestSubjects->merge($electiveSubjects->take(7 - $bestSubjects->count()));
            }

            $totalPoints = $bestSubjects->sum('point');
            $bestMarks = $bestSubjects->pluck('mark')->toArray();
            $gpaResult = StudentResultService::calculateGpaAndDivision($bestMarks); // Your service can stay

            $studentsData[] = [
                'student' => $student,
                'subjectsData' => $subjectsData,
                'bestSubjects' => $bestSubjects,
                'total_points' => $totalPoints,
                'gpa' => $gpaResult['gpa'],
                'division' => $gpaResult['division'],
            ];
        }

        // --- Rank by GPA then total points ---
        usort($studentsData, function($a, $b) {
            if ($a['gpa'] === $b['gpa']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            return $a['gpa'] <=> $b['gpa'];
        });

        foreach ($studentsData as $i => &$data) {
            $data['position'] = $i + 1;
        }
    }

    return $studentsData;
}

}