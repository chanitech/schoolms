<?php

namespace App\Http\Controllers;

use App\Services\AIAnalysisService;
use App\Models\Student;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\PocketTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AIAnalysisController extends Controller
{
    /** @var AIAnalysisService */
    protected $ai;

    public function __construct(AIAnalysisService $ai)
    {
        $this->ai = $ai;

        // These routes have no permission gate and expose whole-school data
        // (any student, class, or finance figures) — guardians only get a
        // menu-hiding middleware elsewhere, which doesn't stop direct URL
        // access, so block the role explicitly here.
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->hasRole('guardian')) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Show the AI analysis dashboard.
     */
    public function index()
    {
        $students = Student::with('class')->orderBy('first_name')->get();
        $classes  = SchoolClass::orderBy('name')->get();

        return view('ai.dashboard', compact('students', 'classes'));
    }

    /**
     * Analyze a single student.
     */
    public function analyzeStudent(Request $request): JsonResponse
{
    $request->validate(['student_id' => 'required|exists:students,id']);
    
    $student = Student::with(['marks.subject', 'marks.grade', 'class'])->findOrFail($request->student_id);
    
    $data = $this->ai->buildStudentPayload($student);

    try {
        $analysis = $this->ai->analyzeStudentPerformance($data);
    } catch (\Exception $e) {
        $analysis = "Error: " . $e->getMessage();
    }

    return response()->json(['analysis' => $analysis]);
}

    /**
     * Analyze class performance.
     */
    public function analyzeClass(Request $request): JsonResponse
{
    $request->validate(['class_id' => 'required|exists:school_classes,id']);

    $marks = Mark::whereHas('student', fn($q) => $q->where('class_id', $request->class_id))
                 ->with('subject', 'student')
                 ->get();

    if ($marks->isEmpty()) {
        return response()->json(['analysis' => 'No marks found for this class.']);
    }

    $subjects = $marks->groupBy('subject.name')->map(fn($m) => [
        'average'  => round($m->avg('score'), 2),
        'pass_rate' => round($m->where('score', '>=', 50)->count() / $m->count() * 100, 2),
        'highest'  => $m->max('score'),
        'lowest'   => $m->min('score'),
    ])->toArray();

    $data = [
        'class_id' => $request->class_id,  // For caching
        'subjects' => $subjects,
    ];

    try {
        $analysis = $this->ai->analyzeClassPerformance($data);
    } catch (\Exception $e) {
        $analysis = "Error: " . $e->getMessage();
    }

    return response()->json(['analysis' => $analysis]);
}

    /**
     * Get intervention suggestions for struggling students.
     */
    public function suggestInterventions(Request $request): JsonResponse
    {
        $request->validate(['class_id' => 'required|exists:school_classes,id']);

        // Get students with average score below 40
        $struggling = Student::where('class_id', $request->class_id)
            ->whereHas('marks')
            ->with('marks')
            ->get()
            ->filter(fn($s) => $s->marks->avg('score') < 40)
            ->map(fn($s) => [
                'name'    => $s->first_name . ' ' . $s->last_name,
                'average' => round($s->marks->avg('score'), 2),
            ])
            ->values();

        if ($struggling->isEmpty()) {
            return response()->json(['analysis' => 'No struggling students found (all averages are 40 or above).']);
        }

        $analysis = $this->ai->suggestInterventions($struggling);

        return response()->json(['analysis' => $analysis]);
    }

    /**
     * Clear AI analysis cache for a given type.
     */
    public function clearCache(Request $request): JsonResponse
    {
        $type = $request->input('type', 'all');

        if ($type === 'finance' || $type === 'all') {
            Cache::forget('ai_finance_' . date('Y-m-d'));
        }
        if ($type === 'student' || $type === 'all') {
            // Student caches are per-student-id; flush via tag pattern not needed —
            // user clears their own by simply re-running after marks update.
            Cache::flush(); // safe for local; in prod use tagged cache
        }
        if ($type === 'class' || $type === 'all') {
            Cache::flush();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get financial insights.
     */
    public function financeInsights(): JsonResponse
    {
        $totalDeposits    = PocketTransaction::where('type', 'deposit')->sum('amount');
        $totalWithdrawals = PocketTransaction::where('type', 'withdrawal')->sum('amount');

        $data = [
            'total_deposits'    => (float) $totalDeposits,
            'total_withdrawals'  => (float) $totalWithdrawals,
            'net_balance'        => (float) ($totalDeposits - $totalWithdrawals),
        ];

        $analysis = $this->ai->analyzeFinance($data);

        return response()->json(['analysis' => $analysis]);
    }
}