<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AIAnalysisService;
use App\Services\CareerGuidance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Counseling Office → Career Guidance: predicted A-level combinations,
 * careers and typical university courses per student.
 * Counselors, academic staff and the head see every class (permission
 * "view career guidance"); class teachers see only the class they lead.
 */
class CareerGuidanceController extends Controller
{
    public function __construct(private CareerGuidance $guidance) {}

    private function seesAll(): bool
    {
        return Auth::user()->can('view career guidance');
    }

    private function classes()
    {
        return SchoolClass::orderBy('name')
            ->unless($this->seesAll(), fn ($q) => $q->where('class_teacher_id', Auth::user()->staff?->id ?? 0))
            ->get();
    }

    private function authorizeStudent(Student $student): void
    {
        abort_unless($this->seesAll() || $this->classes()->contains('id', $student->class_id), 403,
            'You can only open career guidance for students in your own class.');
    }

    /** Which sections this viewer may see on a student page. */
    private function sections(): array
    {
        $u = Auth::user();
        return [
            'marks'     => true,
            'aptitude'  => $this->seesAll() || $u->can('view aptitude tests'),
            'interests' => $this->seesAll() || $u->can('view interest inventories'),
        ];
    }

    public function index(Request $request)
    {
        $classes = $this->classes();
        abort_if($classes->isEmpty() && ! $this->seesAll(), 403, 'Career guidance is available to counselors, academic staff, the head of school and class teachers.');

        $class = $classes->firstWhere('id', (int) $request->query('class_id')) ?? $classes->first();
        $rows = $class ? $this->guidance->classOverview($class) : collect();

        // How the class splits by strongest area, and by top predicted combination
        $byArea = $rows->pluck('strongest')->filter()->countBy()->sortDesc();
        $byCombo = $rows->map(fn ($r) => array_key_first($r['top']))->filter()->countBy()->sortDesc();

        $combo = $request->query('combo');
        $status = $request->query('status');
        $filtered = $rows
            ->when($combo, fn ($c) => $c->filter(fn ($r) => array_key_exists($combo, $r['top'])))
            ->when($status === 'ready', fn ($c) => $c->filter(fn ($r) => collect($r['top'])->first()['ready'] ?? false))
            ->when($status === 'support', fn ($c) => $c->filter(fn ($r) => ! (collect($r['top'])->first()['ready'] ?? false)));

        return view('career_guidance.index', [
            'classes' => $classes, 'class' => $class, 'rows' => $filtered, 'total' => $rows->count(),
            'byArea' => $byArea, 'byCombo' => $byCombo, 'combo' => $combo, 'status' => $status,
            'combinations' => CareerGuidance::combinations(), 'credit' => $this->guidance->creditMark(),
        ]);
    }

    public function show(Student $student)
    {
        $this->authorizeStudent($student);
        $a = $this->guidance->analyse($student, $this->sections());

        return view('career_guidance.show', ['a' => $a, 'tcuNote' => config('career.tcu_note')]);
    }

    /** AI-written advice from the same evidence; cached per student until their data changes. */
    public function advice(Student $student, AIAnalysisService $ai): JsonResponse
    {
        $this->authorizeStudent($student);
        $evidence = $this->guidance->evidenceText($this->guidance->analyse($student, $this->sections()));

        $prompt = "You are an experienced career guidance teacher in a Tanzanian secondary school. "
            . CareerGuidance::ADVICE_INSTRUCTIONS
            . " Use clear headings and short bullet points. Write in English.\n\nEVIDENCE:\n" . $evidence;

        $text = $ai->ask($prompt, 'career:' . $student->id . ':' . md5($evidence), 60 * 24);

        return response()->json(['advice' => $text]);
    }

    public function report(Student $student)
    {
        $this->authorizeStudent($student);
        $a = $this->guidance->analyse($student, $this->sections());

        $pdf = Pdf::loadView('career_guidance.report', ['a' => $a, 'tcuNote' => config('career.tcu_note')])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('career-guidance-' . str($student->first_name . '-' . $student->last_name)->slug() . '.pdf');
    }
}
