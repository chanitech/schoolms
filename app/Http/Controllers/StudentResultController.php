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
use App\Models\Department;
use App\Models\AcademicSession;








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
    $this->authorize('export results');

    // --- Get filters ---
    $classId = $request->input('class_id');
    $examId = $request->input('exam_id');
    $departmentId = $request->input('department_id');
    $academicSessionId = $request->input('academic_session_id');

    // --- Fetch students filtered by class & academic session ---
    $students = Student::where('class_id', $classId)
        ->where('academic_session_id', $academicSessionId)
        ->get();

    // --- Fetch subjects optionally filtered by department ---
    $subjects = $departmentId
        ? Subject::where('department_id', $departmentId)->get()
        : Subject::all();

    $grades = Grade::all();
    $school = SchoolInfo::first();

    $studentsData = [];

    foreach ($students as $student) {
        // --- Get marks for this student and exam ---
        $marks = Mark::where('student_id', $student->id)
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('subject_id'); // easier lookup

        $subjectsData = [];

        foreach ($subjects as $subject) {
            $markValue = $marks[$subject->id]->mark ?? null;
            $grade = $grades->firstWhere(fn($g) => $markValue !== null && $markValue >= $g->min_mark && $markValue <= $g->max_mark);

            $subjectsData[$subject->id] = [
                'subject' => $subject->name,
                'mark' => $markValue ?? '-',
                'grade' => $grade->name ?? '-',
                'point' => $grade->point ?? 0,
                'type' => $subject->type ?? 'core',
            ];
        }

        // --- NECTA Best 7 (core first, then electives) ---
        $coreSubjects = collect($subjectsData)->where('type', 'core')->sortByDesc('mark');
        $electiveSubjects = collect($subjectsData)->where('type', 'elective')->sortByDesc('mark');

        $bestSubjects = $coreSubjects->take(7);
        if ($bestSubjects->count() < 7) {
            $bestSubjects = $bestSubjects->merge($electiveSubjects->take(7 - $bestSubjects->count()));
        }

        $totalPoints = $bestSubjects->sum('point');
        $bestMarks = $bestSubjects->pluck('mark')->filter(fn($m) => $m !== '-')->toArray();
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

    // --- Sort by total points ascending (lowest points = first position) ---
    usort($studentsData, function ($a, $b) {
        return $a['total_points'] <=> $b['total_points']; // lowest first
    });

    // --- Assign positions ---
    foreach ($studentsData as $i => &$data) {
        $data['position'] = $i + 1;
    }

    // --- Generate PDF ---
    $pdf = Pdf::loadView('results.class_results_pdf', [
        'studentsData' => $studentsData,
        'subjects' => $subjects,
        'grades' => $grades,
        'school' => $school,
        'class' => SchoolClass::find($classId),
        'exam' => Exam::find($examId),
    ])
    ->setPaper('A3', 'landscape')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    // --- Optional watermark ---
    $pdf->getDomPDF()->getCanvas()->page_text(
        520, 800,
        $school->name ?? 'SCHOOL NAME',
        'Arial', 50, [0.85, 0.85, 0.85], 0.5
    );

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












private function getClassResultsDataWithSubjects(Request $request)
{
    $selectedClassId = $request->input('class_id');
    $selectedExamId = $request->input('exam_id');
    $selectedDepartmentId = $request->input('department_id');
    $selectedAcademicSessionId = $request->input('academic_session_id');

    $studentsData = [];

    if ($selectedClassId && $selectedExamId && $selectedAcademicSessionId) {
        // Fetch students based on filters
        $students = Student::where('class_id', $selectedClassId)
            ->where('academic_session_id', $selectedAcademicSessionId)
            ->get();

        $grades = Grade::all();

        // Limit subjects by department if selected
        $subjectQuery = Subject::query();
        if ($selectedDepartmentId) {
            $subjectQuery->where('department_id', $selectedDepartmentId);
        }
        $subjects = $subjectQuery->get();

        // Fetch department ranking rule
        $department = $selectedDepartmentId ? Department::find($selectedDepartmentId) : null;
        $requires7Subjects = $department?->rank_requires_7_subjects ?? true;

        foreach ($students as $student) {
            $marksQuery = $student->marks()
                ->where('exam_id', $selectedExamId)
                ->with('subject');

            if ($selectedDepartmentId) {
                $marksQuery->whereIn('subject_id', $subjects->pluck('id'));
            }

            $marks = $marksQuery->get();
            $subjectsData = [];

            foreach ($marks as $mark) {
                $grade = $grades->firstWhere(fn($g) =>
                    $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark
                );

                $subjectsData[] = [
                    'subject_id' => $mark->subject->id,
                    'name' => $mark->subject->name,
                    'type' => $mark->subject->type ?? 'core',
                    'mark' => $mark->mark,
                    'point' => $grade->point ?? 0,
                    'grade' => $grade->name ?? '-',
                ];
            }

            // NECTA "Best 7" logic
            $core = collect($subjectsData)->where('type', 'core')->sortByDesc('mark');
            $electives = collect($subjectsData)->where('type', 'elective')->sortByDesc('mark');
            $bestSubjects = $core->take(7);
            if ($bestSubjects->count() < 7) {
                $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
            }

            $totalPoints = $bestSubjects->sum('point');
            $bestMarks = $bestSubjects->pluck('mark')->toArray();
            $gpaResult = \App\Services\StudentResultService::calculateGpaAndDivision($bestMarks);

            $studentsData[] = [
                'student' => $student,
                'subjectsData' => $subjectsData,
                'bestSubjects' => $bestSubjects,
                'total_points' => $totalPoints,
                'gpa' => $gpaResult['gpa'],
                'division' => $gpaResult['division'],
                // Only assign rank if department allows it or student has >= 7 subjects
                'eligible_for_rank' => $requires7Subjects ? $bestSubjects->count() >= 7 : true,
            ];
        }

        // Sort students by total points
        $studentsData = collect($studentsData)->sortBy('total_points')->values();

        // Assign positions
        $position = 1;
        $previousPoints = null;

        $studentsData = $studentsData->map(function ($item, $index) use (&$position, &$previousPoints) {
            if (!$item['eligible_for_rank']) {
                $item['position'] = '-';
                return $item;
            }

            if ($previousPoints !== null && $item['total_points'] > $previousPoints) {
                $position = $index + 1;
            }

            $item['position'] = $position;
            $previousPoints = $item['total_points'];
            return $item;
        });
    }

    return $studentsData;
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
    $studentsData = $this->getClassResultsDataWithSubjects($request);

    if (empty($studentsData)) {
        return redirect()->back()->with('error', 'No results found for the selected filters.');
    }

    // Fetch related models
    $exam = Exam::find($request->input('exam_id'));
    $class = SchoolClass::find($request->input('class_id'));
    $academicSession = AcademicSession::find($request->input('academic_session_id'));
    $department = $request->input('department_id') ? Department::find($request->input('department_id')) : null;
    $school = SchoolInfo::first(); // Fetch school info dynamically

    $pdf = PDF::loadView('results.pdf.marksheet', compact(
        'studentsData', 
        'exam', 
        'class', 
        'academicSession', 
        'department', 
        'school'
    ))
    ->setPaper('a4', 'landscape'); // Set landscape A4

    return $pdf->stream('Student_Results.pdf');
}

}