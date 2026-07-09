<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Mark;
use App\Models\StudentResult;
use App\Models\Department;
use App\Models\AcademicSession;
use App\Services\StudentResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\Payment;
use App\Models\SchoolInfo;


class GuardianController extends Controller
{
    // ────────────────────────────
    //  ADMIN CRUD METHODS
    // ────────────────────────────

    public function index()

    {
        
        $guardians = Guardian::with('user', 'students')->paginate(10);
        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        // Students without a guardian (or all active – you decide)
        $unlinkedStudents = Student::whereNull('guardian_id')->get();
        return view('guardians.create', compact('unlinkedStudents'));
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'gender'              => 'required|in:male,female',
            'relation_to_student' => 'required|string|max:50',
            'phone'               => ['required', 'string', 'max:20', Rule::unique('guardians', 'phone')->where('school_id', $schoolId)],
            'email'               => 'required|email|unique:users,email',
            'address'             => 'nullable|string|max:255',
            'occupation'          => 'nullable|string|max:100',
            'national_id'         => ['nullable', 'string', 'max:50', Rule::unique('guardians', 'national_id')->where('school_id', $schoolId)],
            'student_ids'         => 'nullable|array',
            'student_ids.*'       => 'exists:students,id',
        ]);

        // 1. Create user account — default password is the guardian's own
        // phone number (matches the existing convention used by the
        // guardian self-registration API), so it's something they already
        // know rather than a separate secret to hand over.
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->phone),
        ]);
        $user->assignRole('guardian');

        // 2. Create guardian record
        $guardian = Guardian::create([
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'gender'              => $request->gender,
            'relation_to_student' => $request->relation_to_student,
            'phone'               => $request->phone,
            'email'               => $request->email,
            'address'             => $request->address,
            'occupation'          => $request->occupation,
            'national_id'         => $request->national_id,
            'user_id'             => $user->id,
        ]);

        // 3. Link children
        if ($request->has('student_ids')) {
            Student::whereIn('id', $request->student_ids)
                ->update(['guardian_id' => $guardian->id]);
        }

        return redirect()->route('guardians.index')
            ->with('success', 'Guardian created successfully.')
            ->with('new_staff_credentials', [
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'password'    => $user->phone,
                'password_note' => 'their phone number',
                'school_code' => app()->bound('currentSchool') ? app('currentSchool')->slug : null,
            ]);
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students', 'user');
        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        $guardian->load('students');
        // Get currently linked students IDs
        $linkedIds = $guardian->students->pluck('id')->toArray();

        // All students, but you could also filter out students who already have a different guardian
        $allStudents = Student::where(function ($q) use ($linkedIds) {
            $q->whereNull('guardian_id')
              ->orWhereIn('id', $linkedIds);
        })->get();

        return view('guardians.edit', compact('guardian', 'allStudents', 'linkedIds'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'gender'              => 'required|in:male,female',
            'relation_to_student' => 'required|string|max:50',
            'phone'               => ['required', 'string', 'max:20', Rule::unique('guardians', 'phone')->ignore($guardian->id)->where('school_id', $schoolId)],
            'email'               => 'required|email|unique:users,email,'.$guardian->user_id,
            'address'             => 'nullable|string|max:255',
            'occupation'          => 'nullable|string|max:100',
            'national_id'         => ['nullable', 'string', 'max:50', Rule::unique('guardians', 'national_id')->ignore($guardian->id)->where('school_id', $schoolId)],
            'student_ids'         => 'nullable|array',
            'student_ids.*'       => 'exists:students,id',
        ]);

        // Update user
        if ($guardian->user) {
            $guardian->user->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'name'       => $request->first_name . ' ' . $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
            ]);
        }

        // Update guardian
        $guardian->update($request->only([
            'first_name', 'last_name', 'gender', 'relation_to_student',
            'phone', 'email', 'address', 'occupation', 'national_id'
        ]));

        // Sync children: unlink all, then re-link selected
        Student::where('guardian_id', $guardian->id)->update(['guardian_id' => null]);
        if ($request->has('student_ids')) {
            Student::whereIn('id', $request->student_ids)->update(['guardian_id' => $guardian->id]);
        }

        return redirect()->route('guardians.index')->with('success', 'Guardian updated successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        // Unlink children before deleting
        Student::where('guardian_id', $guardian->id)->update(['guardian_id' => null]);

        // Delete associated user
        if ($guardian->user) {
            $guardian->user->delete();
        }

        $guardian->delete();

        return redirect()->route('guardians.index')->with('success', 'Guardian deleted successfully.');
    }

    // ────────────────────────────
    //  GUARDIAN PORTAL METHODS
    // ────────────────────────────

    /**
     * Dashboard – list of the guardian’s children.
     */
   /**
 * Dashboard – list of the guardian’s children with financial summaries.
 */
public function dashboard()
{
    $user = Auth::user();
    $guardian = Guardian::where('user_id', $user->id)->first();

    if (!$guardian) {
        abort(404, 'Guardian profile not found.');
    }

    $schoolInfo = SchoolInfo::first();
    $lockEnabled = $schoolInfo->lock_results_for_guardians ?? true;
    $onlyOverdue = $schoolInfo->lock_results_only_overdue ?? false;

    $students = $guardian->students()
        ->with([
            'class',
            'studentBills',
            'pocketTransactions' => function ($q) {
                $q->latest()->limit(1);
            },
        ])
        ->get()
        ->map(function ($student) use ($lockEnabled, $onlyOverdue) {
            $totalBilled = $student->studentBills->sum('total_amount');
            $totalPaid   = $student->studentBills->sum('amount_paid');
            $outstanding = $totalBilled - $totalPaid;

            $lastPocket = $student->pocketTransactions->first();
            $pocketBalance = $lastPocket ? $lastPocket->balance_after : 0;

            $student->outstanding_fees = $outstanding;
            $student->total_paid       = $totalPaid;
            $student->pocket_balance   = $pocketBalance;
            $student->total_billed     = $totalBilled;

            // Determine lock status
            $locked = false;
            if ($lockEnabled) {
                if ($onlyOverdue) {
                    $locked = $student->studentBills->contains(function ($bill) {
                        return $bill->amount_paid < $bill->total_amount
                               && $bill->due_date
                               && $bill->due_date->isPast();
                    });
                } else {
                    $locked = $outstanding > 0;
                }
            }
            $student->results_locked = $locked;

            return $student;
        });

    return view('guardian.dashboard', compact('guardian', 'students'));
}

/**
 * Fees page – detailed financial breakdown per child.
 */
public function fees()
{
    $user = Auth::user();
    $guardian = Guardian::where('user_id', $user->id)->first();

    if (!$guardian) {
        abort(404, 'Guardian profile not found.');
    }

    $students = $guardian->students()
        ->with([
            'class',
            'studentBills.bill',               // eager load bill name
            'payments' => function ($q) {
                $q->latest()->limit(5);        // recent 5 payments
            },
            'pocketTransactions' => function ($q) {
                $q->latest()->limit(5);        // recent 5 pocket transactions
            },
        ])
        ->get()
        ->map(function ($student) {
            $totalBilled = $student->studentBills->sum('total_amount');
            $totalPaid   = $student->studentBills->sum('amount_paid');
            $outstanding = $totalBilled - $totalPaid;

            $lastPocket = $student->pocketTransactions->first();
            $pocketBalance = $lastPocket ? $lastPocket->balance_after : 0;

            $student->outstanding    = $outstanding;
            $student->total_paid     = $totalPaid;
            $student->pocket_balance = $pocketBalance;

            return $student;
        });

    return view('guardian.fees', compact('guardian', 'students'));
}



public function paymentReceipt(Payment $payment)
{
    $user = Auth::user();
    $guardian = Guardian::where('user_id', $user->id)->first();

    if (!$guardian) {
        abort(404, 'Guardian profile not found.');
    }

    $isChild = $guardian->students()->where('id', $payment->student_id)->exists();
    if (!$isChild) {
        abort(403, 'Unauthorized – this payment is not for your child.');
    }

    // Correct relationship name is 'user', not 'recordedBy'
    $payment->load([
        'student.schoolClass',
        'studentBill.bill',
        'user',   // <-- changed from 'recordedBy'
    ]);

    return view('guardian.payment_receipt', compact('payment'));
}

 /**
  * AI performance insight for one of the guardian's own children only.
  */
 public function aiInsight(Student $student, \App\Services\AIAnalysisService $ai)
 {
    $user = Auth::user();
    $guardian = Guardian::where('user_id', $user->id)->first();

    if (!$guardian || $student->guardian_id !== $guardian->id) {
        abort(403, 'Unauthorized – this is not your child.');
    }

    $student->load(['marks.subject', 'marks.grade', 'class']);

    try {
        $analysis = $ai->analyzeStudentPerformance($ai->buildStudentPayload($student));
    } catch (\Exception $e) {
        $analysis = "Error: " . $e->getMessage();
    }

    return response()->json(['analysis' => $analysis]);
 }

 public function showResult(Student $student, Request $request)
{
    $user = Auth::user();
    $guardian = Guardian::where('user_id', $user->id)->first();

    if (!$guardian || $student->guardian_id !== $guardian->id) {
        abort(403, 'Unauthorized – this is not your child.');
    }

    // Reuse the same logic as StudentResultController@show
    try {
        $grades      = Grade::all();
        $departments = Department::all();
        $sessions    = AcademicSession::all();

        $selectedSessionId    = $request->input('academic_session_id')
                             ?? $request->input('session_id');
        $selectedExam         = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;
        $selectedDepartmentId = $request->input('department_id');
        $department           = $selectedDepartmentId ? Department::find($selectedDepartmentId) : null;

        $requires7 = $department ? ($department->rank_requires_7_subjects ?? true) : true;

        // Guardians can only select published exams
        if ($selectedExam && !$selectedExam->isPublished()) {
            $selectedExam = null;
        }

        // Exams scoped to session — guardians only see published ones
        $examsQuery = Exam::where('status', 'published');
        if ($selectedSessionId) {
            $examsQuery->where('academic_session_id', $selectedSessionId);
        }
        $exams = $examsQuery->orderBy('id')->get();

        // Subjects in department (for X/7 display)
        $deptSubjectsQuery = Subject::query();
        if ($selectedDepartmentId) {
            $deptSubjectsQuery->where('department_id', $selectedDepartmentId);
        }
        $totalDeptSubjects = $deptSubjectsQuery->count();

        $subjectsData        = collect();
        $result              = ['gpa' => null, 'division' => '-'];
        $totalPoints         = null;
        $rank                = '-';
        $isIncomplete        = false;
        $attemptedCount      = 0;
        $requiredCount       = $requires7 ? 7 : 1;
        $gpaTrend            = collect();
        $subjectTrend        = [];
        $bestSubjectsOverall = [];

        if ($selectedExam) {
            $subjectIds = $deptSubjectsQuery->pluck('id')->toArray();

            $marks = $student->marks()
                ->with('subject')
                ->where('exam_id', $selectedExam->id)
                ->when($selectedDepartmentId, fn($q) => $q->whereIn('subject_id', $subjectIds))
                ->get();

            if ($marks->isNotEmpty()) {
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

                // Best 7 by points (NECTA)
                $coreSubjects = $subjectsData->where('type', 'core')->sortBy('point');
                $electives    = $subjectsData->where('type', 'elective')->sortBy('point');
                $bestSubjects = $coreSubjects->take(7)
                    ->merge($electives->take(max(0, 7 - $coreSubjects->take(7)->count())));

                $attemptedCount = $bestSubjects->count();
                $eligible        = !($requires7 && $attemptedCount < 7);
                $isIncomplete    = !$eligible;

                $rawTotalPoints = $bestSubjects->sum('point');
                $rawGpa         = $attemptedCount ? round($rawTotalPoints / $attemptedCount, 2) : 0;

                if ($eligible) {
                    $result      = StudentResultService::calculateFromPoints($rawTotalPoints, $attemptedCount);
                    $totalPoints = $rawTotalPoints;
                } else {
                    $result      = ['gpa' => null, 'division' => '-'];
                    $totalPoints = null;
                }

                // Persist to DB
                StudentResult::updateOrCreate(
                    ['student_id' => $student->id, 'exam_id' => $selectedExam->id],
                    [
                        'gpa'           => $rawGpa,
                        'total_points'  => $rawTotalPoints,
                        'division'      => $eligible ? ($result['division'] ?? '-') : '-',
                        'department_id' => $selectedDepartmentId,
                    ]
                );

                // Rank among eligible classmates
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

                        if ($requires7 && $sBest->count() < 7) continue;

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

                // GPA trend (session exams)
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

                // Subject trend (session exams)
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

                // Best subjects overall (session exams)
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
        }

        return view('guardian.result', [
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
            'totalPoints'            => $totalPoints,
            'rank'                   => $rank,
            'isIncomplete'           => $isIncomplete,
            'attemptedCount'         => $attemptedCount,
            'requiredCount'          => $requiredCount,
            'gpaTrend'               => $gpaTrend,
            'subjectTrend'           => $subjectTrend,
            'bestSubjectsOverall'    => $bestSubjectsOverall,
        ]);
    } catch (\Throwable $e) {
        Log::error('Guardian showResult error: ' . $e->getMessage());
        return back()->with('error', 'Something went wrong while loading results.');
    }
}

    
}