<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\StudentResult;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Enrollment;
use App\Imports\MarksImport;
use App\Exports\MarksTemplateExport;
use App\Exports\MarksTemplateWithStudentsExport;

class StudentResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view results')->only(['index', 'show', 'classResults']);
        $this->middleware('permission:export results')->only([
            'exportExcel', 'exportPDF',
            'exportExcelNoticeBoard', 'exportPDFNoticeBoard',
        ]);
    }

    // ==================== INDEX ====================

    public function index(Request $request)
    {
        $this->authorize('view results');

        $classes  = SchoolClass::all();
        $sessions = AcademicSession::all();

        $sessionId = $request->input('session_id') ?? $request->input('academic_session_id');
        $classId   = $request->input('class_id');

        if ($classId && $sessionId) {
            $enrollmentsQuery = Enrollment::with('student')
                ->where('class_id', $classId)
                ->where('academic_session_id', $sessionId)
                ->where('status', 'active');

            $studentsPaginated = $enrollmentsQuery->paginate(10)->withQueryString();
            $students = $studentsPaginated->setCollection(
                $studentsPaginated->getCollection()->map(fn($en) => $en->student)
            );
        } else {
            $query = Student::with(['class', 'academicSession']);
            if ($classId)   $query->where('class_id', $classId);
            if ($sessionId) $query->where('academic_session_id', $sessionId);
            $students = $query->paginate(10)->withQueryString();
        }

        return view('results.index', compact('students', 'classes', 'sessions'));
    }

    // ==================== CLASS RESULTS ====================

    public function classResults(Request $request)
    {
        $this->authorize('view results');

        $classes          = SchoolClass::all();
        $exams            = Exam::all();
        $departments      = Department::all();
        $academicSessions = AcademicSession::all();

        $selectedClassId           = $request->input('class_id');
        $selectedExamId            = $request->input('exam_id');
        $selectedDepartmentId      = $request->input('department_id');
        $selectedAcademicSessionId = $request->input('academic_session_id') ?? $request->input('session_id');

        // Gate: only show results for published exams to non-staff
        $selectedExamObj = $selectedExamId ? Exam::find($selectedExamId) : null;
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isStaff = $authUser->hasAnyRole(['Admin','Academic','HOD','Teacher','HR','Principal']);
        if ($selectedExamObj && !$selectedExamObj->isPublished() && !$isStaff) {
            return view('results.class_results', compact(
                'classes', 'exams', 'departments', 'academicSessions',
                'selectedClassId', 'selectedExamId', 'selectedDepartmentId', 'selectedAcademicSessionId'
            ))->with('resultNotPublished', true)->with('studentsData', collect());
        }

        $studentsData = $this->getClassResultsDataWithSubjects($request);

        $topPerformer   = null;
        $topAverage     = 0;
        $topStudentData = null;
        if ($studentsData->isNotEmpty()) {
            // Top performer = first eligible ranked student
            $first          = $studentsData->where('eligible_for_rank', true)->first()
                           ?? $studentsData->first();
            $topStudentData = $first;
            $topPerformer   = $first['student'];
            $topAverage     = $first['average_mark'] ?? 0;
        }

        $subjectGradeCounts = [];
        foreach ($studentsData as $student) {
            foreach ($student['subjectsData'] as $subject) {
                $name  = $subject['name'];
                $grade = $subject['grade'] ?? '';
                if (!isset($subjectGradeCounts[$name])) {
                    $subjectGradeCounts[$name] = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                }
                if (in_array($grade, ['A', 'B', 'C', 'D', 'F'])) {
                    $subjectGradeCounts[$name][$grade]++;
                }
            }
        }

        $subjects = $selectedDepartmentId
            ? Subject::where('department_id', $selectedDepartmentId)->get()
            : Subject::all();

        $examIsPublished = $selectedExamObj ? $selectedExamObj->isPublished() : true;

        return view('results.class_results', compact(
            'classes', 'exams', 'departments', 'academicSessions',
            'studentsData', 'subjects',
            'selectedClassId', 'selectedExamId', 'selectedDepartmentId', 'selectedAcademicSessionId',
            'subjectGradeCounts', 'topPerformer', 'topAverage', 'topStudentData',
            'examIsPublished', 'selectedExamObj'
        ));
    }

    // ==================== STUDENT RESULT DETAIL ====================

    public function show(Student $student, Request $request)
    {
        $this->authorize('view results');

        try {
            $grades      = Grade::all();
            $departments = Department::all();
            $sessions    = AcademicSession::all();

            $selectedSessionId    = $request->input('academic_session_id')
                                 ?? $request->input('session_id');
            $selectedExam         = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;
            $selectedDepartmentId = $request->input('department_id');
            $department           = $selectedDepartmentId ? Department::find($selectedDepartmentId) : null;

            // Requires 7 subjects for rank/division?
            $requires7 = $department ? ($department->rank_requires_7_subjects ?? true) : true;

            // Scope exams to selected session so trends are session-specific
            $examsQuery = Exam::query();
            if ($selectedSessionId) {
                $examsQuery->where('academic_session_id', $selectedSessionId);
            }
            $exams = $examsQuery->orderBy('id')->get();

            // Total subjects in this department (or all) — used for "X/7" display
            $deptSubjectsQuery = Subject::query();
            if ($selectedDepartmentId) {
                $deptSubjectsQuery->where('department_id', $selectedDepartmentId);
            }
            $totalDeptSubjects = $deptSubjectsQuery->count();

            // Defaults
            $subjectsData        = collect();
            $result              = ['gpa' => null, 'division' => '-'];
            $totalPoints         = null;   // null = display '-'
            $rank                = '-';
            $isIncomplete        = false;
            $attemptedCount      = 0;
            $requiredCount       = $requires7 ? 7 : 1;
            $gpaTrend            = collect();
            $subjectTrend        = [];
            $bestSubjectsOverall = [];

            // Gate: non-admin users can only view published results
            if ($selectedExam && !$selectedExam->isPublished()) {
                /** @var \App\Models\User $authUser */
                $authUser = Auth::user();
                $isStaff = $authUser->hasAnyRole(['Admin', 'Academic', 'HOD', 'Teacher', 'HR', 'Principal']);
                if (!$isStaff) {
                    $exam = $selectedExam;
                    return view('results.show', compact(
                        'student', 'grades', 'departments', 'sessions', 'exams',
                        'exam', 'selectedSessionId', 'selectedDepartmentId',
                        'subjectsData', 'result', 'totalPoints', 'rank', 'isIncomplete',
                        'attemptedCount', 'requiredCount', 'gpaTrend', 'subjectTrend', 'bestSubjectsOverall'
                    ))->with('resultNotPublished', true)
                      ->with('selected_exam_id', $selectedExam->id ?? null)
                      ->with('selected_session_id', $selectedSessionId)
                      ->with('selected_department_id', $selectedDepartmentId);
                }
            }

            if ($selectedExam) {
                $subjectIds = $deptSubjectsQuery->pluck('id')->toArray();

                $marks = $student->marks()
                    ->with('subject')
                    ->where('exam_id', $selectedExam->id)
                    ->when($selectedDepartmentId, fn($q) => $q->whereIn('subject_id', $subjectIds))
                    ->get();

                if ($marks->isEmpty()) {
                    return back()->with('warning', 'No marks found for this student in the selected exam or department.');
                }

                $subjectsData = $marks->map(function ($mark) use ($grades, $student, $selectedExam) {
                    $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);

                    $subjectMarks = Mark::where('subject_id', $mark->subject_id)
                        ->where('exam_id', $selectedExam->id)
                        ->orderByDesc('mark')
                        ->pluck('student_id')
                        ->toArray();

                    $subjectPosition = ($pos = array_search($student->id, $subjectMarks)) !== false
                        ? $pos + 1 : '-';

                    return [
                        'subject'          => $mark->subject->name ?? 'Unknown',
                        'type'             => $mark->subject->type ?? 'core',
                        'mark'             => $mark->mark,
                        'grade'            => $grade->name        ?? '-',
                        'point'            => $grade->point       ?? 0,
                        'remark'           => $grade->description ?? '',
                        'subject_position' => $subjectPosition,
                    ];
                });

                // Best 7 by lowest points (NECTA)
                $coreSubjects = $subjectsData->where('type', 'core')->sortBy('point');
                $electives    = $subjectsData->where('type', 'elective')->sortBy('point');
                $bestSubjects = $coreSubjects->take(7)
                    ->merge($electives->take(max(0, 7 - $coreSubjects->take(7)->count())));

                $attemptedCount = $bestSubjects->count();

                // ── Division + Rank eligibility ───────────────────────────────
                // Dept requires 7 AND student has < 7  →  incomplete → all display as '-'
                // Dept doesn't require 7 OR student has ≥ 7  →  calculate normally
                $eligible     = !($requires7 && $attemptedCount < 7);
                $isIncomplete = !$eligible;

                $rawTotalPoints = $bestSubjects->sum('point');
                $rawGpa         = $attemptedCount ? round($rawTotalPoints / $attemptedCount, 2) : 0;

                if ($eligible) {
                    $result      = StudentResultService::calculateFromPoints($rawTotalPoints, $attemptedCount);
                    $totalPoints = $rawTotalPoints;  // show real value
                } else {
                    // Incomplete: store real values to DB but display '-' in blade
                    $result      = ['gpa' => null, 'division' => '-'];
                    $totalPoints = null;  // blade checks null → shows '-'
                }

                // Persist to DB (always store real values regardless of eligibility)
                StudentResult::updateOrCreate(
                    ['student_id' => $student->id, 'exam_id' => $selectedExam->id],
                    [
                        'gpa'           => $rawGpa,
                        'total_points'  => $rawTotalPoints,
                        'division'      => $eligible
                            ? ($result['division'] ?? '-')
                            : '-',
                        'department_id' => $selectedDepartmentId,
                    ]
                );

                // Rank — only for eligible students, only vs other eligible classmates
                if ($eligible) {
                    $rankingMethod = config('results.ranking_method', 'average');
                    $classStudents = Student::where('class_id', $student->class_id)->get();
                    $rankData      = [];

                    foreach ($classStudents as $s) {
                        $sMarks = $s->marks()->with('subject')
                            ->where('exam_id', $selectedExam->id)
                            ->when($selectedDepartmentId, fn($q) => $q->whereIn('subject_id', $subjectIds))
                            ->get();

                        if ($sMarks->isEmpty()) continue;

                        $sData = $sMarks->map(fn($m) => [
                            'mark'  => $m->mark,
                            'point' => ($g = $grades->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark))
                                ? $g->point : 0,
                            'type'  => $m->subject->type ?? 'core',
                        ]);

                        $sCore     = $sData->where('type', 'core')->sortBy('point');
                        $sElective = $sData->where('type', 'elective')->sortBy('point');
                        $sBest     = $sCore->take(7)->merge($sElective->take(max(0, 7 - $sCore->take(7)->count())));

                        if ($requires7 && $sBest->count() < 7) continue; // skip incomplete classmates

                        $sAvg = $sBest->count() ? round($sBest->sum('mark') / $sBest->count(), 2) : 0;
                        $sPts = $sBest->sum('point');
                        $rankData[$s->id] = ['avg' => $sAvg, 'pts' => $sPts];
                    }

                    if ($rankingMethod === 'points') {
                        uasort($rankData, fn($a, $b) =>
                            $a['pts'] !== $b['pts'] ? $a['pts'] <=> $b['pts'] : $b['avg'] <=> $a['avg']
                        );
                    } else {
                        uasort($rankData, fn($a, $b) =>
                            $a['avg'] !== $b['avg'] ? $b['avg'] <=> $a['avg'] : $a['pts'] <=> $b['pts']
                        );
                    }

                    $positions = [];
                    $rankNum   = 0;
                    $prevAvg   = null;
                    $prevPts   = null;
                    foreach ($rankData as $id => $data) {
                        if ($prevAvg === null || $data['avg'] != $prevAvg || $data['pts'] != $prevPts) {
                            $rankNum++;
                        }
                        $positions[$id] = $rankNum;
                        $prevAvg = $data['avg'];
                        $prevPts = $data['pts'];
                    }

                    $totalClassSize = Student::where('class_id', $student->class_id)->count();
                    $studentRank    = $positions[$student->id] ?? null;
                    $rank = $studentRank ? $studentRank . '/' . $totalClassSize : '-';
                }

                // ── GPA trend (session-scoped exams only) ──────────────────
                $gpaTrend = $exams->map(function ($exam) use ($student, $grades) {
                    $examMarks = $student->marks()->with('subject')->where('exam_id', $exam->id)->get();
                    if ($examMarks->isEmpty()) return null;

                    $examData  = $examMarks->map(fn($m) => [
                        'mark'  => $m->mark,
                        'point' => ($g = $grades->firstWhere(fn($gr) => $m->mark >= $gr->min_mark && $m->mark <= $gr->max_mark))
                            ? $g->point : 0,
                        'type'  => $m->subject->type ?? 'core',
                    ]);
                    $core      = $examData->where('type', 'core')->sortBy('point');
                    $electives = $examData->where('type', 'elective')->sortBy('point');
                    $best      = $core->take(7)->merge($electives->take(max(0, 7 - $core->take(7)->count())));
                    $pts       = $best->sum('point');
                    $res       = StudentResultService::calculateFromPoints($pts, $best->count());
                    return ['exam' => $exam->name, 'gpa' => $res['gpa']];
                })->filter();

                // ── Subject trend (session-scoped exams only) ──────────────
                // Only subjects this student actually sat — no noise from other dept subjects
                $sessionSubjects = $subjectsData->pluck('subject')->unique()->values();
                foreach ($sessionSubjects as $subjectName) {
                    $subject = Subject::where('name', $subjectName)->first();
                    if (!$subject) continue;

                    $trendData = $exams->map(fn($exam) => [
                        'exam' => $exam->name,
                        'mark' => Mark::where([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'exam_id'    => $exam->id,
                        ])->value('mark'),
                    ])->filter(fn($e) => $e['mark'] !== null)->values();

                    if ($trendData->isNotEmpty()) {
                        $subjectTrend[$subjectName] = $trendData;
                    }
                }

                // ── Best subjects overall (across session exams) ───────────
                foreach ($sessionSubjects as $subjectName) {
                    $subject = Subject::where('name', $subjectName)->first();
                    if (!$subject) continue;

                    $subjectMarks = Mark::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->whereIn('exam_id', $exams->pluck('id'))
                        ->pluck('mark');

                    if ($subjectMarks->isEmpty()) continue;

                    $bestSubjectsOverall[] = [
                        'subject'    => $subjectName,
                        'average'    => round($subjectMarks->avg(), 2),
                        'highest'    => $subjectMarks->max(),
                        'lowest'     => $subjectMarks->min(),
                        'exam_count' => $subjectMarks->count(),
                    ];
                }

                $bestSubjectsOverall = collect($bestSubjectsOverall)->sortByDesc('average')->values();
            }

            return view('results.show', [
                'student'                => $student,
                'exam'                   => $selectedExam,
                'exams'                  => $exams,
                'departments'            => $departments,
                'sessions'               => $sessions,
                'selected_exam_id'       => $selectedExam->id ?? null,
                'selected_department_id' => $selectedDepartmentId,
                'selected_session_id'    => $selectedSessionId,
                'subjectsData'           => $subjectsData,
                'result'                 => $result,
                'totalPoints'            => $totalPoints,       // null when incomplete
                'rank'                   => $rank,
                'isIncomplete'           => $isIncomplete,      // NEW: blade uses this
                'attemptedCount'         => $attemptedCount,    // NEW: how many subjects sat
                'requiredCount'          => $requiredCount,     // NEW: how many needed (7 or 1)
                'gpaTrend'               => $gpaTrend,
                'subjectTrend'           => $subjectTrend,
                'bestSubjectsOverall'    => $bestSubjectsOverall,
            ]);

        } catch (\Throwable $e) {
            Log::error('StudentResult show error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while calculating results.');
        }
    }

    // ==================== IMPORT / EXPORT ====================

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'class_id'   => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id'    => 'required|exists:exams,id',
        ]);

        try {
            $import = new MarksImport(
                $request->class_id, $request->session_id,
                $request->subject_id, $request->exam_id
            );
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errors       = $import->getErrors();

            if ($successCount == 0 && count($errors) > 0) {
                return back()->with('error', 'Import failed: ' . implode(', ', array_slice($errors, 0, 3)));
            } elseif (count($errors) > 0) {
                return back()->with('warning', "Imported $successCount records. Issues: " . implode(', ', array_slice($errors, 0, 3)));
            }
            return back()->with('success', "Successfully imported $successCount marks.");
        } catch (\Exception $e) {
            Log::error('Excel import error: ' . $e->getMessage());
            return back()->with('error', 'System error: ' . $e->getMessage());
        }
    }

    public function downloadFilteredTemplate(Request $request)
    {
        $request->validate([
            'class_id'            => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'exam_id'             => 'required|exists:exams,id',
            'subject_id'          => 'required|exists:subjects,id',
        ]);

        return Excel::download(new MarksTemplateWithStudentsExport($request), 'marks_entry_template.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MarksTemplateExport(), 'marks_import_template.xlsx');
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('export results');

        $studentsData = $this->getClassResultsDataWithSubjects($request);
        if ($studentsData->isEmpty()) {
            return back()->with('warning', 'No students found for the selected filters.');
        }

        $classId              = $request->input('class_id');
        $selectedDepartmentId = $request->input('department_id');

        $classSubjects = Subject::whereHas('classes', fn($q) => $q->where('class_id', $classId))
            ->when($selectedDepartmentId, fn($q) => $q->where('department_id', $selectedDepartmentId))
            ->get()
            ->sortBy([
                fn($a, $b) => ($a->type === 'core' ? 0 : 1) <=> ($b->type === 'core' ? 0 : 1),
                fn($a, $b) => strcmp($a->name, $b->name),
            ])->values();

        return Excel::download(new ClassResultsExport($studentsData->toArray(), $classSubjects), 'class_results.xlsx');
    }

    public function exportPDF(Request $request)
    {
        $this->authorize('export results');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        try {
            $studentsData = $this->getClassResultsDataWithSubjects($request);
            if ($studentsData->isEmpty()) {
                return back()->with('warning', 'No students found for the selected filters.');
            }

            $classId              = $request->input('class_id');
            $selectedDepartmentId = $request->input('department_id');

            $classSubjects = Subject::whereHas('classes', fn($q) => $q->where('class_id', $classId))
                ->when($selectedDepartmentId, fn($q) => $q->where('department_id', $selectedDepartmentId))
                ->get()
                ->sortBy([
                    fn($a, $b) => ($a->type === 'core' ? 0 : 1) <=> ($b->type === 'core' ? 0 : 1),
                    fn($a, $b) => strcmp($a->name, $b->name),
                ])->values();

            $class           = SchoolClass::find($classId);
            $exam            = Exam::find($request->input('exam_id'));
            $academicSession = AcademicSession::find($request->input('academic_session_id'));

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

            $html = view('results.class_results_pdf', [
                'studentsData'    => $studentsData,
                'classSubjects'   => $classSubjects,
                'class'           => $class,
                'exam'            => $exam,
                'academicSession' => $academicSession,
                'school'          => SchoolInfo::first(),
                'grades'          => Grade::orderByDesc('min_mark')->get(),
            ])->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('A3', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                    'tempDir'              => $tempDir,
                    'chroot'              => public_path(),
                    'defaultFont'          => 'sans-serif',
                    'dpi'                  => 96,
                ]);

            return $pdf->download('class_results.pdf');
        } catch (\Exception $e) {
            Log::error('PDF export failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF.');
        }
    }

    public function showExportForm()
    {
        return view('results.export', [
            'classes'     => SchoolClass::all(),
            'exams'       => Exam::all(),
            'sessions'    => AcademicSession::all(),
            'departments' => Department::all(),
        ]);
    }

  public function exportResultsPdf(Request $request)
{
    ini_set('memory_limit', '2048M');
    set_time_limit(1800);

    $classId      = $request->input('class_id');
    $sessionId    = $request->input('academic_session_id');
    $departmentId = $request->input('department_id');

    $class      = SchoolClass::findOrFail($classId);
    $session    = AcademicSession::findOrFail($sessionId);
    $department = $departmentId ? Department::find($departmentId) : null;

    // ── Dynamic school info ──────────────────────────
    $schoolInfo = \App\Models\SchoolInfo::first();

    $school = (object)[
        'name'    => $schoolInfo->name    ?? 'MEMA ASEP Learning Centre',
        'motto'   => $schoolInfo->motto   ?? 'Maadili, Elimu, Maendeleo, Amani',
        'address' => $schoolInfo->address ?? 'Kisarawe, Pwani',
        'phone'   => $schoolInfo->phone   ?? '+255',
        'email'   => $schoolInfo->email   ?? 'info@mema.or.tz',
        'website' => $schoolInfo->website ?? 'www.mema.ac.tz',
    ];

    // ── Logos & watermark ────────────────────────────
    $logoLeftPath  = $schoolInfo && $schoolInfo->logo
                        ? public_path($schoolInfo->logo)
                        : public_path('vendor/adminlte/dist/img/MEMA.png');
    $logoRightPath = public_path('vendor/adminlte/dist/img/MEMA.webp');  // can also be stored in SchoolInfo
    $watermarkPath = $schoolInfo && $schoolInfo->logo
                        ? public_path($schoolInfo->logo)
                        : public_path('vendor/adminlte/dist/img/MEMA.png');

    $logoLeft  = file_exists($logoLeftPath)  ? 'data:image/png;base64,'  . base64_encode(file_get_contents($logoLeftPath))  : null;
    $logoRight = file_exists($logoRightPath) ? 'data:image/webp;base64,' . base64_encode(file_get_contents($logoRightPath)) : null;
    $watermark = file_exists($watermarkPath) ? 'data:image/png;base64,'  . base64_encode(file_get_contents($watermarkPath)) : null;

    $subjects   = $department
        ? Subject::where('department_id', $department->id)->get()
        : Subject::all();
    $subjectIds = $subjects->pluck('id')->all();

    $grades    = Grade::all()->sortByDesc('min_mark')->values();
    $divisions = Division::all();

    $exams = Exam::where('academic_session_id', $sessionId)
        ->where(function ($q) {
            $q->where('include_in_term_final', 1)
              ->orWhere('include_in_year_final', 1)
              ->orWhere('is_terminal_exam', 1)
              ->orWhere('is_annual_exam', 1);
        })
        ->orderByRaw("FIELD(term, 'Term 1', 'Term 2')")
        ->orderBy('is_annual_exam', 'asc')
        ->orderBy('id')
        ->get();
    $examIds = $exams->pluck('id')->all();

    $findGrade = function (?float $mark) use ($grades) {
        if ($mark === null) return null;
        foreach ($grades as $g) {
            if ($mark >= $g->min_mark && $mark <= $g->max_mark) {
                return $g;
            }
        }
        return null;
    };

    $studentsData = collect();
    $chunkSize    = 50;

    Student::where('class_id', $classId)
        ->where('academic_session_id', $sessionId)
        ->chunk($chunkSize, function ($studentsChunk) use (
            $subjects, $subjectIds, $exams, $examIds, $findGrade, $divisions, &$studentsData
        ) {
            $studentIds = $studentsChunk->pluck('id')->all();

            $marksRaw = Mark::whereIn('student_id', $studentIds)
                ->whereIn('exam_id', $examIds)
                ->whereIn('subject_id', $subjectIds)
                ->get();

            $marksBy = [];
            foreach ($marksRaw as $m) {
                $marksBy[$m->student_id][$m->exam_id][$m->subject_id] = $m;
            }

            foreach ($studentsChunk as $student) {
                $studentRow             = ['student' => $student, 'exams' => []];
                $totalMarksAcrossExams  = 0.0;
                $totalPointsAcrossExams = 0.0;

                foreach ($exams as $exam) {
                    $subjectsData = collect();

                    foreach ($subjects as $subject) {
                        $m        = $marksBy[$student->id][$exam->id][$subject->id] ?? null;
                        $markVal  = $m ? $m->mark : null;
                        $gradeRow = $findGrade($markVal);

                        $subjectsData->push([
                            'subject_id' => $subject->id,
                            'name'       => $subject->name,
                            'mark'       => $markVal,
                            'grade'      => $gradeRow->name  ?? '-',
                            'point'      => $gradeRow->point ?? 0,
                            'type'       => $subject->type   ?? 'core',
                        ]);
                    }

                    $core      = $subjectsData->where('type', 'core')->filter(fn($s) => $s['mark'] !== null)->sortBy('point');
                    $electives = $subjectsData->where('type', 'elective')->filter(fn($s) => $s['mark'] !== null)->sortBy('point');
                    $bestSubjects = $core->take(7)->merge($electives->take(max(0, 7 - $core->take(7)->count())));

                    $totalPoints = $bestSubjects->sum('point');
                    $gpaResult   = StudentResultService::calculateFromPoints($totalPoints, $bestSubjects->count());

                    $studentRow['exams'][$exam->id] = [
                        'exam'           => $exam,
                        'subjectsData'   => $subjectsData,
                        'bestSubjects'   => $bestSubjects->values(),
                        'total_marks'    => $bestSubjects->sum('mark'),
                        'total_points'   => $totalPoints,
                        'gpa'            => $gpaResult['gpa'],
                        'division'       => $gpaResult['division'],
                        'is_annual_exam' => (bool) $exam->is_annual_exam,
                    ];

                    $totalMarksAcrossExams  += $bestSubjects->sum('mark');
                    $totalPointsAcrossExams += $totalPoints;
                }

                $examCount = $exams->count() ?: 1;

                $studentRow['total_marks']  = $totalMarksAcrossExams;
                $studentRow['total_points'] = round($totalPointsAcrossExams / $examCount, 2);

                $overallResult = StudentResultService::calculateFromPoints(
                    (int) round($totalPointsAcrossExams / $examCount),
                    7
                );
                $studentRow['gpa']      = $overallResult['gpa'];
                $studentRow['division'] = $overallResult['division'];
                $studentRow['position'] = null;

                $studentsData->push($studentRow);
            }
        });

    // Rank by total_marks (unchanged for multi‑exam PDF)
    $lastMarks    = null;
    $lastPosition = null;
    $studentsData = $studentsData->sortByDesc('total_marks')->values()
        ->map(function ($student, $index) use (&$lastMarks, &$lastPosition) {
            if ($lastMarks === null || $student['total_marks'] < $lastMarks) {
                $position     = $index + 1;
                $lastPosition = $position;
            } else {
                $position = $lastPosition;
            }
            $lastMarks          = $student['total_marks'];
            $student['position'] = $position;
            return $student;
        });

    $pdf = Pdf::loadView('results.pdf.marksheet_multi', [
        'studentsData' => $studentsData,
        'class'        => $class,
        'session'      => $session,
        'department'   => $department,
        'subjects'     => $subjects,
        'exams'        => $exams,
        'logoLeft'     => $logoLeft,
        'logoRight'    => $logoRight,
        'watermark'    => $watermark,
        'grades'       => $grades,
        'school'       => $school,    // <-- passed to blade
    ])
    ->setPaper('a4', 'landscape')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream("Class_{$class->name}_Results.pdf");
}

    // ==================== AJAX ====================

    public function getClassesByDepartment(Request $request)
    {
        $request->validate(['department_id' => 'required|exists:departments,id']);
        $classes = SchoolClass::whereHas('subjects', function ($q) use ($request) {
            $q->where('department_id', $request->department_id);
        })->get(['id', 'name']);
        return response()->json($classes);
    }

    // ==================== TERMINAL REPORT ====================

    public function terminalReport(Request $request)
    {
        return view('results.terminal_report', [
            'classes'     => SchoolClass::all(),
            'exams'       => Exam::all(),
            'sessions'    => AcademicSession::all(),
            'departments' => Department::all(),
        ]);
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Division rules:
     *   '-'          = incomplete (dept requires 7 subjects, student attempted fewer)
     *   '0','I'..'IV' = from DB Division table (student attempted ≥7)
     *
     * Rank rules:
     *   Only eligible students (≥7 when required) are ranked.
     *   Ineligible → position = '-', excluded from rank pool.
     */
    private function getClassResultsDataWithSubjects(Request $request): Collection
    {
        $classId      = $request->input('class_id');
        $examId       = $request->input('exam_id');
        $departmentId = $request->input('department_id');
        $sessionId    = $request->input('academic_session_id') ?? $request->input('session_id');

        if (!($classId && $examId && $sessionId)) return collect();

        $enrollments = Enrollment::with('student')
            ->where('class_id', $classId)
            ->where('academic_session_id', $sessionId)
            ->where('status', 'active')
            ->get();

        if ($enrollments->isEmpty()) return collect();

        $students          = $enrollments->pluck('student');
        $grades            = Grade::all();
        $subjects          = $departmentId ? Subject::where('department_id', $departmentId)->get() : Subject::all();
        $department        = $departmentId ? Department::find($departmentId) : null;
        $requires7Subjects = $department?->rank_requires_7_subjects ?? true;

        $marks = Mark::whereIn('student_id', $students->pluck('id'))
            ->where('exam_id', $examId)
            ->when($departmentId, fn($q) => $q->whereIn('subject_id', $subjects->pluck('id')))
            ->with('subject')
            ->get()
            ->groupBy('student_id');

        $studentsData = collect();

        foreach ($students as $student) {
            $studentMarks = $marks->get($student->id) ?? collect();
            $subjectsData = collect();

            foreach ($studentMarks as $mark) {
                if (!$mark->subject) continue;
                $grade = $grades->firstWhere(fn($g) => $mark->mark >= $g->min_mark && $mark->mark <= $g->max_mark);
                $subjectsData->put($mark->subject->id, [
                    'subject_id' => $mark->subject->id,
                    'name'       => $mark->subject->name,
                    'type'       => $mark->subject->type ?? 'core',
                    'mark'       => $mark->mark,
                    'point'      => $grade->point ?? 0,
                    'grade'      => $grade->name  ?? '-',
                ]);
            }

            $core         = $subjectsData->where('type', 'core')->filter(fn($s) => $s['mark'] !== null)->sortBy('point');
            $electives    = $subjectsData->where('type', 'elective')->filter(fn($s) => $s['mark'] !== null)->sortBy('point');
            $bestSubjects = $core->take(7)->merge($electives->take(max(0, 7 - $core->take(7)->count())));

            $rawTotalPoints = $bestSubjects->sum('point');
            $averageMark    = $bestSubjects->count()
                ? round($bestSubjects->sum('mark') / $bestSubjects->count(), 2) : 0;

            $eligible = !($requires7Subjects && $bestSubjects->count() < 7);

            if ($eligible) {
                $gpaResult   = StudentResultService::calculateFromPoints($rawTotalPoints, $bestSubjects->count());
                $totalPoints = $rawTotalPoints;
            } else {
                // Incomplete: suppress division; keep real points in DB but null for rank pool
                $gpaResult = [
                    'gpa'      => $bestSubjects->count() ? round($rawTotalPoints / $bestSubjects->count(), 2) : 0,
                    'division' => '-',
                ];
                $totalPoints = null; // excluded from rank calculation
            }

            $studentsData->push([
                'student'           => $student,
                'subjectsData'      => $subjectsData,
                'bestSubjects'      => $bestSubjects,
                'average_mark'      => $averageMark,
                'total_points'      => $eligible ? $rawTotalPoints : null,  // null = incomplete display
                'raw_total_points'  => $rawTotalPoints,                      // always real, for DB
                'gpa'               => $gpaResult['gpa'],
                'division'          => $gpaResult['division'],
                'eligible_for_rank' => $eligible,
                'attempted_count'   => $bestSubjects->count(),
            ]);
        }

        $rankingMethod    = config('results.ranking_method', 'average');
        $eligibleStudents = $studentsData->where('eligible_for_rank', true);
        $ineligible       = $studentsData->where('eligible_for_rank', false);

        $eligibleSorted = $rankingMethod === 'points'
            ? $eligibleStudents->sortBy([['total_points', 'asc'], ['average_mark', 'desc']])->values()
            : $eligibleStudents->sortBy([['average_mark', 'desc'], ['total_points', 'asc']])->values();

        $position = 0;
        $prevAvg  = null;
        $prevPts  = null;

        $ranked = $eligibleSorted->map(function ($item) use (&$position, &$prevAvg, &$prevPts) {
            $currAvg = $item['average_mark'];
            $currPts = $item['total_points'];
            if ($prevAvg === null || $currAvg != $prevAvg || $currPts != $prevPts) {
                $position++;
            }
            $item['position'] = $position;
            $prevAvg = $currAvg;
            $prevPts = $currPts;
            return $item;
        });

        $unranked = $ineligible->map(function ($item) {
            $item['position'] = '-';
            return $item;
        })->values();

        $sorted = $ranked->concat($unranked);

        // Persist — always store real points to DB
        $sorted->each(function ($row) use ($examId, $departmentId) {
            if (!isset($row['student'])) return;
            StudentResult::updateOrCreate(
                ['student_id' => $row['student']->id, 'exam_id' => $examId],
                [
                    'gpa'           => $row['gpa']              ?? 0,
                    'total_points'  => $row['raw_total_points'] ?? 0,
                    'average_mark'  => $row['average_mark']     ?? 0,
                    'division'      => $row['division']         ?? '-',
                    'department_id' => $departmentId,
                    'position'      => is_numeric($row['position']) ? $row['position'] : null,
                ]
            );
        });

        return $sorted;
    }
}