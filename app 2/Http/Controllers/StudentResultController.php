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
use App\Models\SchoolClass;
use App\Models\Division;
use App\Models\SchoolInfo;
use App\Models\Department;
use App\Models\AcademicSession;
use Illuminate\Support\Collection;
use App\Models\School; // <- Add this
use Barryvdh\DomPDF\Facade\Pdf; // <- Add this for PDF
use Illuminate\Support\Facades\DB;
use App\Models\Session;





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

    // --- Fetch filter dropdown data ---
    $classes = SchoolClass::all();
    $exams = Exam::all();
    $departments = Department::all();
    $academicSessions = \App\Models\AcademicSession::all();

    // --- Get selected filters ---
    $selectedClassId = $request->input('class_id');
    $selectedExamId = $request->input('exam_id');
    $selectedDepartmentId = $request->input('department_id');
    $selectedAcademicSessionId = $request->input('academic_session_id');

    // --- Fetch students + results ---
    $studentsData = $this->getClassResultsDataWithSubjects($request);

    // --- Filter subjects optionally by department ---
    $subjects = $selectedDepartmentId
        ? Subject::where('department_id', $selectedDepartmentId)->get()
        : Subject::all();

    return view('results.class_results', compact(
        'classes',
        'exams',
        'departments',
        'academicSessions',
        'studentsData',
        'subjects',
        'selectedClassId',
        'selectedExamId',
        'selectedDepartmentId',
        'selectedAcademicSessionId'
    ));
}



    /**
     * Show detailed student result.
     */
public function show(Student $student, Request $request)
{
    $this->authorize('view results');

    try {
        // Fetch all exams, grades, and departments for filtering
        $exams = Exam::all();
        $grades = Grade::all();
        $departments = Department::all();

        // Selected filters
        $selectedExam = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;
        $selectedDepartmentId = $request->input('department_id');

        // Initialize defaults
        $subjectsData = collect();
        $result = ['gpa' => 0, 'division' => '-'];
        $totalPoints = 0;
        $rank = '-';
        $gpaTrend = collect();
        $subjectTrend = [];
        $bestSubjectsOverall = [];

        if ($selectedExam) {
            // Filter subjects by department if selected
            $subjectQuery = Subject::query();
            if ($selectedDepartmentId) {
                $subjectQuery->where('department_id', $selectedDepartmentId);
            }
            $subjectIds = $subjectQuery->pluck('id')->toArray();

            // Fetch marks limited to selected department’s subjects
            $marks = $student->marks()
                ->with('subject')
                ->where('exam_id', $selectedExam->id)
                ->when($selectedDepartmentId, fn($q) => $q->whereIn('subject_id', $subjectIds))
                ->get();

            if ($marks->isEmpty()) {
                return back()->with('warning', 'No marks found for this student in the selected exam or department.');
            }

            // Prepare subjects data
            $subjectsData = $marks->map(function ($mark) use ($grades, $student, $selectedExam) {
                $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);
                $subjectMarks = Mark::where('subject_id', $mark->subject_id)
                    ->where('exam_id', $selectedExam->id)
                    ->orderByDesc('mark')
                    ->pluck('student_id')
                    ->toArray();
                $subjectPosition = ($pos = array_search($student->id, $subjectMarks)) !== false ? $pos + 1 : '-';

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

            // Best 7 NECTA calculation
            $coreSubjects = $subjectsData->where('type', 'core')->sortByDesc('mark');
            $electives = $subjectsData->where('type', 'elective')->sortByDesc('mark');
            $bestSubjects = $coreSubjects->take(7)->merge($electives->take(7 - $coreSubjects->take(7)->count()));
            $totalPoints = $bestSubjects->sum('point');
            $bestMarks = $bestSubjects->pluck('mark')->toArray();
            $result = StudentResultService::calculateGpaAndDivision($bestMarks);

            // Save student result
            StudentResult::updateOrCreate(
                ['student_id' => $student->id, 'exam_id' => $selectedExam->id],
                [
                    'gpa' => $result['gpa'],
                    'total_points' => $totalPoints,
                    'division' => $result['division'],
                    'department_id' => $selectedDepartmentId,
                ]
            );

            // Class rank
            $classStudents = Student::where('class_id', $student->class_id)->get();
            $positions = [];
            foreach ($classStudents as $s) {
                $sMarks = $s->marks()->with('subject')
                    ->where('exam_id', $selectedExam->id)
                    ->when($selectedDepartmentId, fn($q) => $q->whereIn('subject_id', $subjectIds))
                    ->get();
                if ($sMarks->isEmpty()) continue;
                $sSubjectsData = $sMarks->map(fn($m) => [
                    'mark' => $m->mark,
                    'point' => ($g = $grades->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark)) ? $g->point : 0,
                    'type' => $m->subject->type ?? 'core',
                ]);
                $sCore = $sSubjectsData->where('type', 'core')->sortByDesc('mark');
                $sElectives = $sSubjectsData->where('type', 'elective')->sortByDesc('mark');
                $sBest = $sCore->take(7)->merge($sElectives->take(7 - $sCore->take(7)->count()));
                $positions[$s->id] = $sBest->sum('point');
            }
            asort($positions);
            $rankPosition = array_search($student->id, array_keys($positions));
            $rank = $rankPosition !== false ? ($rankPosition + 1) . '/' . count($positions) : '-';

            // GPA Trend
            $gpaTrend = $exams->map(function ($exam) use ($student, $grades) {
                $examMarks = $student->marks()->with('subject')->where('exam_id', $exam->id)->get();
                if ($examMarks->isEmpty()) return null;
                $examSubjectsData = $examMarks->map(fn($m) => [
                    'mark' => $m->mark,
                    'point' => ($g = Grade::all()->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark)) ? $g->point : 0,
                    'type' => $m->subject->type ?? 'core',
                ]);
                $core = $examSubjectsData->where('type', 'core')->sortByDesc('mark');
                $electives = $examSubjectsData->where('type', 'elective')->sortByDesc('mark');
                $best = $core->take(7)->merge($electives->take(7 - $core->take(7)->count()));
                return ['exam' => $exam->name, 'gpa' => StudentResultService::calculateGpaAndDivision($best->pluck('mark')->toArray())['gpa']];
            })->filter();

            // Subject Trend across all exams
            $allSubjects = Subject::all();
            foreach ($allSubjects as $subject) {
                $marksByExam = $exams->map(fn($exam) => [
                    'exam' => $exam->name,
                    'mark' => Mark::where(['student_id' => $student->id, 'subject_id' => $subject->id, 'exam_id' => $exam->id])->value('mark') ?? 0
                ]);
                $subjectTrend[$subject->name] = $marksByExam;
            }

            // Best Subjects Overall
            foreach ($allSubjects as $subject) {
                $marks = Mark::where('student_id', $student->id)->where('subject_id', $subject->id)->pluck('mark');
                if ($marks->isEmpty()) continue;
                $bestSubjectsOverall[] = [
                    'subject' => $subject->name,
                    'average' => $marks->avg(),
                    'highest' => $marks->max(),
                    'lowest' => $marks->min(),
                    'exam_count' => $marks->count(),
                ];
            }

            // Sort best subjects by average descending
            $bestSubjectsOverall = collect($bestSubjectsOverall)->sortByDesc('average')->values();
        }

        return view('results.show', [
            'student' => $student,
            'exam' => $selectedExam,
            'exams' => $exams,
            'departments' => $departments,
            'selected_exam_id' => $selectedExam->id ?? null,
            'selected_department_id' => $selectedDepartmentId,
            'subjectsData' => $subjectsData,
            'result' => $result,
            'totalPoints' => $totalPoints,
            'rank' => $rank,
            'gpaTrend' => $gpaTrend,
            'subjectTrend' => $subjectTrend,
            'bestSubjectsOverall' => $bestSubjectsOverall,
        ]);

    } catch (\Throwable $e) {
        //\Log::error('Error calculating student results: ' . $e->getMessage());
        return back()->with('error', 'Something went wrong while calculating results.');
    }
}







/**
 * Excel export.
 */
public function exportExcel(Request $request)
{
    $this->authorize('export results');

    $studentsData = $this->getClassResultsDataWithSubjects($request); // returns Collection
    if ($studentsData->isEmpty()) {
        return back()->with('warning', 'No students found for the selected filters.');
    }

    $selectedDepartmentId = $request->input('department_id');
    $subjects = $selectedDepartmentId
        ? Subject::where('department_id', $selectedDepartmentId)->get()
        : Subject::all();

    // Convert Collections to arrays only when passing to the export constructor
    return Excel::download(
        new ClassResultsExport($studentsData->toArray(), $subjects->toArray()),
        'class_results.xlsx'
    );
}

/**
 * PDF Export for class.
 */
public function exportPDF(Request $request)
{
    $this->authorize('export results');

    $studentsData = $this->getClassResultsDataWithSubjects($request);
    if ($studentsData->isEmpty()) {
        return back()->with('warning', 'No students found for the selected filters.');
    }

    $selectedDepartmentId = $request->input('department_id');
    $subjects = $selectedDepartmentId
        ? Subject::where('department_id', $selectedDepartmentId)->get()
        : Subject::all();

    $grades = Grade::all();
   // $school = SchoolInfo::first();
    $class = SchoolClass::find($request->input('class_id'));
    $exam = Exam::find($request->input('exam_id'));

    $pdf = Pdf::loadView('results.class_results_pdf', [
        'studentsData' => $studentsData, // Collections work fine in Blade
        'subjects' => $subjects,
        'grades' => $grades,
        //'school' => $school,
        'class' => $class,
        'exam' => $exam,
    ])
    ->setPaper('A3', 'landscape')
    ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

    return $pdf->download('class_results.pdf');
}








public function exportClassIndividualPDF(Request $request)
{
    $studentsData = $this->getClassResultsDataWithSubjects($request);

    if (empty($studentsData)) {
        return back()->with('warning', 'No students found for the selected filters.');
    }

    $pdfs = [];

    foreach ($studentsData as $data) {
        $pdf = Pdf::loadView('results.individual_report', [
            'student' => $data['student'],
            'exam' => Exam::find($request->exam_id),
            'subjectsData' => $data['subjectsData'],
            'totalPoints' => $data['total_points'],
            'gpa' => $data['gpa'],
            'division' => $data['division'],
            'position' => $data['position'],
            'totalStudents' => count($studentsData),
        ]);

        // Save individual PDF to temp folder
        $filename = 'reports/' . $data['student']->first_name . '_' . $data['student']->last_name . '.pdf';
        $pdf->save(storage_path('app/public/' . $filename));

        $pdfs[] = storage_path('app/public/' . $filename);
    }

    // Optional: merge all PDFs into a single bulk PDF (requires additional package)
    // Or, you can zip all PDFs and return download
    $zipFile = storage_path('app/public/reports/Class_Reports.zip');
    $zip = new \ZipArchive();
    if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
        foreach ($pdfs as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    }

    return response()->download($zipFile);
}


private function getClassResultsDataWithSubjects(Request $request): Collection
{
    $selectedClassId = $request->input('class_id');
    $selectedExamId = $request->input('exam_id');
    $selectedDepartmentId = $request->input('department_id');
    $selectedAcademicSessionId = $request->input('academic_session_id');

    if (!($selectedClassId && $selectedExamId && $selectedAcademicSessionId)) {
        return collect(); // empty collection
    }

    $students = Student::where('class_id', $selectedClassId)
        ->where('academic_session_id', $selectedAcademicSessionId)
        ->get();

    $grades = Grade::all();

    $subjectQuery = Subject::query();
    if ($selectedDepartmentId) {
        $subjectQuery->where('department_id', $selectedDepartmentId);
    }
    $subjects = $subjectQuery->get();

    $department = $selectedDepartmentId ? Department::find($selectedDepartmentId) : null;
    $requires7Subjects = $department?->rank_requires_7_subjects ?? true;

    $studentsData = collect();

    foreach ($students as $student) {
        $marksQuery = $student->marks()
            ->where('exam_id', $selectedExamId)
            ->with('subject');

        if ($selectedDepartmentId) {
            $marksQuery->whereIn('subject_id', $subjects->pluck('id'));
        }

        $marks = $marksQuery->get();

        $subjectsData = collect();
        foreach ($marks as $mark) {
            $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

            $subjectsData->put($mark->subject->id, [
                'subject_id' => $mark->subject->id,
                'name' => $mark->subject->name,
                'type' => $mark->subject->type ?? 'core',
                'mark' => is_numeric($mark->mark) ? floatval($mark->mark) : null,
                'point' => $grade->point ?? 0,
                'grade' => $grade->name ?? '-',
            ]);
        }

        // Best 7 subjects
        $core = $subjectsData->where('type', 'core')->filter(fn($s) => $s['mark'] !== null)->sortByDesc('mark');
        $electives = $subjectsData->where('type', 'elective')->filter(fn($s) => $s['mark'] !== null)->sortByDesc('mark');

        $bestSubjects = $core->take(7);
        if ($bestSubjects->count() < 7) {
            $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
        }

        $totalPoints = $bestSubjects->sum('point');
        $totalMarks = $bestSubjects->sum('mark');
        $bestMarksArray = $bestSubjects->pluck('mark')->filter()->values()->toArray();

        // --- SAFE GPA CALCULATION ---
        if (empty($bestMarksArray)) {
            $gpaResult = ['gpa' => 0, 'division' => '-'];
        } else {
            $gpaResult = StudentResultService::calculateGpaAndDivision($bestMarksArray);
        }

        $studentsData->push([
            'student' => $student,
            'subjectsData' => $subjectsData,
            'bestSubjects' => $bestSubjects,
            'total_points' => (int)$totalPoints,
            'total_marks' => (float)$totalMarks,
            'gpa' => $gpaResult['gpa'],
            'division' => $gpaResult['division'],
            'eligible_for_rank' => $requires7Subjects ? ($bestSubjects->count() >= 1) : true,
        ]);
    }

    // --- Ranking ---
    $sorted = $studentsData->sort(function ($a, $b) {
        $diff = ($b['total_marks'] ?? 0) <=> ($a['total_marks'] ?? 0);
        if ($diff !== 0) return $diff;

        return ($b['total_points'] ?? 0) <=> ($a['total_points'] ?? 0);
    })->values();

    $position = 0;
    $skip = 1;
    $prevMarks = null;
    $prevPoints = null;

    $sorted = $sorted->map(function ($item) use (&$position, &$skip, &$prevMarks, &$prevPoints) {
        if (!$item['eligible_for_rank']) {
            $item['position'] = '-';
            return $item;
        }

        $currMarks = $item['total_marks'] ?? 0;
        $currPoints = $item['total_points'] ?? 0;

        if ($prevMarks === null) {
            $position = 1;
            $skip = 1;
        } elseif ($currMarks === $prevMarks && $currPoints === $prevPoints) {
            $skip++;
        } else {
            $position += $skip;
            $skip = 1;
        }

        $item['position'] = $position;

        $prevMarks = $currMarks;
        $prevPoints = $currPoints;

        return $item;
    });

    // --- Save StudentResult safely ---
    $sorted->each(function ($row) use ($selectedExamId, $selectedDepartmentId) {
        if (isset($row['student']) && $row['student'] instanceof Student) {
            try {
                StudentResult::updateOrCreate(
                    ['student_id' => $row['student']->id, 'exam_id' => $selectedExamId],
                    [
                        'gpa' => $row['gpa'] ?? 0,
                        'total_points' => $row['total_points'] ?? 0,
                        'total_marks' => $row['total_marks'] ?? 0,
                        'division' => $row['division'] ?? '-',
                        'department_id' => $selectedDepartmentId,
                        'position' => $row['position'] ?? null,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to update StudentResult for student ' . ($row['student']->id ?? '?') . ': ' . $e->getMessage());
            }
        }
    });

    return $sorted;
}





// Show the filter form page
    public function showExportForm()
    {
        $classes = SchoolClass::all();
        $exams = Exam::all();
        $sessions = AcademicSession::all();
        $departments = Department::all();

        return view('results.export', compact('classes', 'exams', 'sessions', 'departments'));
    }





public function exportResultsPdf(Request $request)
{
    $classId = $request->input('class_id');
    $sessionId = $request->input('academic_session_id');
    $departmentId = $request->input('department_id');

    $class = SchoolClass::findOrFail($classId);
    $session = AcademicSession::findOrFail($sessionId);
    $department = $departmentId ? Department::find($departmentId) : null;

    $subjects = $department
        ? Subject::where('department_id', $department->id)->get()
        : Subject::all();

    $grades = Grade::all();
    $divisions = Division::all();

    $exams = Exam::where('academic_session_id', $sessionId)
        ->where(function($q){
            $q->where('include_in_term_final', 1)
              ->orWhere('is_terminal_exam', 1);
        })
        ->orderBy('term')
        ->orderBy('id')
        ->get();

    $students = Student::where('class_id', $classId)
        ->where('academic_session_id', $sessionId)
        ->get();

    $studentsData = collect();

    foreach ($students as $student) {
        $studentRow = [
            'student' => $student,
            'exams' => [],
        ];

        $totalMarksAcrossExams = 0;
        $totalPointsAcrossExams = 0;

        foreach ($exams as $exam) {
            $subjectsData = collect();
            $marks = Mark::where('student_id', $student->id)
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->where('exam_id', $exam->id)
                ->get();

            foreach ($subjects as $subject) {
                $mark = $marks->firstWhere('subject_id', $subject->id);
                $gradeRow = $grades->first(fn($g) => $mark && $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

                $subjectsData->push([
                    'subject_id' => $subject->id,
                    'name' => $subject->name,
                    'mark' => $mark->mark ?? null,
                    'grade' => $gradeRow->name ?? '-',
                    'point' => $gradeRow->point ?? 0,
                    'type' => $subject->type ?? 'core',
                ]);
            }

            // Best 7 subjects logic
            $core = $subjectsData->where('type', 'core')->filter(fn($s) => $s['mark'] !== null)->sortByDesc('mark');
            $electives = $subjectsData->where('type', 'elective')->filter(fn($s) => $s['mark'] !== null)->sortByDesc('mark');
            $bestSubjects = $core->take(7)->merge($electives->take(7 - $core->take(7)->count()));

            $totalMarks = $bestSubjects->pluck('mark')->sum();
            $totalPoints = $bestSubjects->pluck('point')->sum();
            $gpa = $bestSubjects->count() ? round($totalPoints / $bestSubjects->count(), 2) : 0;

            $studentRow['exams'][$exam->id] = [
                'exam' => $exam,
                'subjectsData' => $subjectsData,
                'bestSubjects' => $bestSubjects,
                'total_marks' => $totalMarks,
                'total_points' => $totalPoints,
                'gpa' => $gpa,
            ];

            $totalMarksAcrossExams += $totalMarks;
            $totalPointsAcrossExams += $totalPoints;
        }

        // Overall totals and GPA
        $studentRow['total_marks'] = $totalMarksAcrossExams;
        $studentRow['total_points'] = $exams->count() ? round($totalPointsAcrossExams / $exams->count(), 2) : 0;
        $studentRow['gpa'] = $subjects->count() ? round($studentRow['total_points'] / 7, 2) : 0; // 7 best per exam

        $divisionRow = $divisions->first(fn($d) => $studentRow['total_points'] >= $d->min_points && $studentRow['total_points'] <= $d->max_points);
        $studentRow['division'] = $divisionRow->name ?? '-';

        // Just set null for now; we'll compute positions later
        $studentRow['position'] = null;

        $studentsData->push($studentRow);
    }

    // --- Calculate Positions ---
    // Sort by total_points descending
    $studentsData = $studentsData
        ->sortByDesc('total_points')
        ->values() // reset keys
        ->map(function($student, $index) {
            $student['position'] = $index + 1;
            return $student;
        });

    $pdf = \PDF::loadView('results.pdf.marksheet_multi', [
        'studentsData' => $studentsData,
        'class' => $class,
        'session' => $session,
        'department' => $department,
        'subjects' => $subjects,
        'exams' => $exams,
    ])->setPaper('a4', 'landscape');

    return $pdf->stream("Class_{$class->name}_Results.pdf");
}


















}

