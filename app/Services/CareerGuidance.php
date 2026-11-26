<?php

namespace App\Services;

use App\Models\AptitudeAnswer;
use App\Models\AptitudeAttempt;
use App\Models\Grade;
use App\Models\InterestInventory;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Career guidance engine shared by the Career Guidance pages, the printed
 * report and the AI assistant: per-subject performance against the class,
 * aptitude by section, interest answers, and how well each A-level
 * combination (config/career.php) fits the student's results.
 */
class CareerGuidance
{
    public const INTEREST_LABELS = [1 => 'Most interesting subject', 2 => 'Most challenging subject', 3 => 'Enjoys most at school',
        6 => 'Activities outside school', 7 => 'Describes self as', 8 => 'Careers of interest', 9 => 'Ideal job', 17 => 'Would like to learn more about'];

    public const ADVICE_INSTRUCTIONS = 'Write career guidance from this evidence: (1) top 2–3 combinations with reasons citing the grades, aptitude and interests; '
        . '(2) careers and typical university degrees each opens; (3) the subjects to improve and the mark needed for a credit; '
        . '(4) one line saying it is advice to discuss with the student, parents and career teacher. '
        . 'Do not invent marks. If evidence is thin (few exams, no aptitude or interests), say what to collect next.';

    private ?float $credit = null;
    private ?Collection $grades = null;

    public function __construct(private AIDataAccess $access) {}

    public static function combinations(): array
    {
        return config('career.combinations', []);
    }

    public function creditMark(): float
    {
        // Lowest mark that still earns a C (credit) on this school's scale; NECTA default 45
        return $this->credit ??= (float) (Grade::where('name', 'C')->value('min_mark') ?? 45);
    }

    public function grade(float $mark): string
    {
        $this->grades ??= Grade::orderByDesc('min_mark')->get(['name', 'min_mark']);
        return optional($this->grades->first(fn ($g) => $mark >= $g->min_mark))->name ?? '-';
    }

    public static function formLevel(?string $className): ?int
    {
        if (! $className || ! preg_match('/form\s*(VI|IV|V|III|II|I|[1-6])\b/i', $className, $m)) return null;
        return ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6][strtoupper($m[1])] ?? (int) $m[1];
    }

    public function findStudent(string $query): ?Student
    {
        $query = trim($query);
        if ($query === '') return null;
        return Student::with('schoolClass')
            ->where(fn ($q) => $q->where('admission_no', $query)->orWhere('id', is_numeric($query) ? (int) $query : 0)
                ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$query}%"])
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]))
            ->first();
    }

    /**
     * Full analysis of one student.
     *
     * @param  array{marks?:bool, aptitude?:bool, interests?:bool}  $can  which sections the viewer may see
     */
    public function analyse(Student $s, array $can = []): array
    {
        $can += ['marks' => true, 'aptitude' => true, 'interests' => true];
        $s->loadMissing('schoolClass');
        $form = self::formLevel($s->schoolClass->name ?? null);

        $subjects = $can['marks'] ? $this->subjectPerformance($s) : [];
        $aptitude = $can['aptitude'] ? $this->aptitude($s) : null;
        $interests = null;
        if ($can['interests']) {
            $inv = InterestInventory::where('student_id', $s->id)->latest('date')->first();
            if ($inv) {
                $interests = ['date' => $inv->date?->format('d/m/Y'), 'answers' => []];
                foreach (self::INTEREST_LABELS as $i => $label) {
                    if (filled($inv->{"q{$i}"})) $interests['answers'][$label] = trim($inv->{"q{$i}"});
                }
            }
        }

        return [
            'student'   => $s,
            'class'     => $s->schoolClass->name ?? 'Unassigned',
            'form'      => $form,
            'can'       => $can,
            'subjects'  => $subjects,
            'areas'     => $this->areas($subjects),
            'aptitude'  => $aptitude,
            'interests' => $interests,
            'fits'      => $this->fits(array_map(fn ($p) => $p['avg'], $subjects), $aptitude),
            'a_level'   => $form && $form >= 5 ? $this->detectALevel($subjects) : null,
            'credit'    => $this->creditMark(),
        ];
    }

    /** Ranked combinations for subject averages ['Physics' => 61.2, ...]. */
    public function fits(array $avgs, ?array $aptitude = null): array
    {
        $credit = $this->creditMark();
        $fits = [];
        foreach (self::combinations() as $code => $c) {
            $needs = $c['o_level'];
            $have = array_values(array_filter($needs, fn ($n) => isset($avgs[$n])));
            $missing = array_values(array_diff($needs, $have));
            $score = $have ? round(array_sum(array_map(fn ($n) => $avgs[$n], $have)) / count($have), 1) : 0.0;
            $gaps = [];
            foreach ($have as $n) if ($avgs[$n] < $credit) $gaps[$n] = round($credit - $avgs[$n], 1);
            $fits[$code] = [
                'code' => $code, 'needs' => $needs, 'missing' => $missing, 'score' => $score, 'gaps' => $gaps,
                'ready' => ! $missing && ! $gaps,
                'aptitude' => $c['aptitude'], 'aptitude_pct' => $aptitude['sections'][$c['aptitude']]['pct'] ?? null,
                'careers' => $c['careers'], 'courses' => $c['courses'],
            ];
        }
        uasort($fits, fn ($a, $b) => (count($a['missing']) <=> count($b['missing'])) ?: ($b['score'] <=> $a['score']));
        return $fits;
    }

    /** Every active student of a class with their top combinations — two queries for the whole class. */
    public function classOverview(SchoolClass $class): Collection
    {
        $students = Student::where('class_id', $class->id)->where('status', 'active')->orderBy('first_name')->get();
        if ($students->isEmpty()) return collect();

        $avgs = Mark::with('subject:id,name')->whereIn('student_id', $students->pluck('id'))->whereNotNull('mark')
            ->selectRaw('student_id, subject_id, AVG(mark) a')->groupBy('student_id', 'subject_id')->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->mapWithKeys(fn ($r) => [($r->subject->name ?? "Subject {$r->subject_id}") => round((float) $r->a, 1)])->all());

        return $students->map(function ($s) use ($avgs) {
            $subj = $avgs[$s->id] ?? [];
            $fits = $this->fits($subj);
            $complete = array_filter($fits, fn ($f) => ! $f['missing']);
            $areas = $this->areas(array_map(fn ($v) => ['avg' => $v], $subj));
            arsort($areas);
            return [
                'student'   => $s,
                'average'   => $subj ? round(array_sum($subj) / count($subj), 1) : null,
                'subjects'  => count($subj),
                'top'       => array_slice($complete ?: $fits, 0, 3, true),
                'strongest' => $areas ? array_key_first($areas) : null,
                'areas'     => $areas,
            ];
        });
    }

    /** Text version of an analysis for the AI. */
    public function evidenceText(array $a): string
    {
        $s = $a['student'];
        $L = ["STUDENT: {$s->first_name} {$s->last_name} (ID {$s->id}), {$a['class']}" . ($a['form'] ? " — Form {$a['form']}" : '')];

        if (! $a['can']['marks']) {
            $L[] = "SUBJECT PERFORMANCE: not available — this user's role cannot see marks.";
        } elseif (! $a['subjects']) {
            $L[] = 'SUBJECT PERFORMANCE: no marks recorded yet.';
        } else {
            $L[] = "\nSUBJECT PERFORMANCE (average of all exams; class average; trend first→latest exam; grade):";
            foreach ($a['subjects'] as $name => $p) {
                $diff = $p['class_avg'] !== null ? sprintf('%+.1f vs class %.1f', $p['avg'] - $p['class_avg'], $p['class_avg']) : 'no class comparison';
                $L[] = sprintf('• %s: %.1f (%s), grade %s, trend %s, %d exam(s)', $name, $p['avg'], $diff, $p['grade'], $p['trend'], $p['exams']);
            }
            $L[] = "\nSTRENGTH BY AREA:";
            foreach ($a['areas'] as $area => $v) $L[] = sprintf('• %s: %.1f', $area, $v);
        }

        if ($ap = $a['aptitude']) {
            $L[] = "\nAPTITUDE TEST ({$ap['date']}): total {$ap['total']} of {$ap['max']} ({$ap['pct']}%)";
            foreach ($ap['sections'] as $sec => $v) $L[] = "• {$sec}: {$v['got']} of {$v['max']} ({$v['pct']}%)";
        } else {
            $L[] = "\nAPTITUDE: " . ($a['can']['aptitude'] ? 'no test taken yet.' : "not available to this user's role.");
        }

        if ($a['can']['interests']) {
            if ($in = $a['interests']) {
                $L[] = "\nINTEREST INVENTORY ({$in['date']}):";
                foreach ($in['answers'] as $label => $ans) $L[] = "• {$label}: {$ans}";
            } else {
                $L[] = "\nINTEREST INVENTORY: not completed yet.";
            }
        }

        if ($a['subjects']) {
            $L[] = "\nA-LEVEL COMBINATION FIT (average of the O-level subjects it builds on; ✓ = credit in all three):";
            foreach ($a['fits'] as $f) {
                $line = sprintf('• %s (%s): %.1f%s', $f['code'], implode(', ', $f['needs']), $f['score'], $f['ready'] ? ' ✓' : '');
                if ($f['missing']) $line .= ' — no record for ' . implode(', ', $f['missing']);
                foreach ($f['gaps'] as $subj => $need) $line .= " — {$subj} needs +{$need} for a credit";
                if ($f['aptitude_pct'] !== null) $line .= " — {$f['aptitude']} aptitude {$f['aptitude_pct']}%";
                $L[] = $line . ". Careers: {$f['careers']}. Typical degrees: " . collect($f['courses'])->pluck(0)->implode('; ');
            }
        }
        if ($a['a_level']) $L[] = "\nDETECTED A-LEVEL COMBINATION: {$a['a_level']}";

        $L[] = "\nCONTEXT: In Tanzania students sit CSEE (NECTA) after Form IV and choose an A-level combination for Form V–VI; "
            . 'selection normally needs at least a credit (C) in the subjects the combination builds on. '
            . "On this school's grading scale a credit (C) starts at {$a['credit']} marks — use this number for targets. "
            . ($a['form'] && $a['form'] <= 2 ? "This student is in Form {$a['form']}, so advise which O-level direction (sciences, business or arts) to strengthen for Form III and which combinations to aim for, not a final choice. " : '')
            . ($a['form'] && $a['form'] >= 5 ? 'This student is already in A-level; focus on university courses and careers that fit their combination and grades. ' : '')
            . config('career.tcu_note');

        return implode("\n", $L);
    }

    /** AI assistant tool: same analysis, sections limited by the user's role. */
    public function forStudent(string $query): array
    {
        if (trim($query) === '') return ['_exact_summary' => 'Which student? Give a name or admission number.'];
        $s = $this->findStudent($query);
        if (! $s) return ['_exact_summary' => "No student found for '{$query}'."];

        $a = $this->analyse($s, [
            'marks' => $this->access->canModel('Mark'),
            'aptitude' => $this->access->canModel('AptitudeAttempt'),
            'interests' => $this->access->canModel('InterestInventory'),
        ]);

        return ['guidance_evidence' => $this->evidenceText($a), 'instructions' => self::ADVICE_INSTRUCTIONS];
    }

    // ── internals ─────────────────────────────────────────────

    private function areas(array $subjects): array
    {
        $out = [];
        foreach (config('career.areas', []) as $area => $list) {
            $vals = array_values(array_filter(array_map(fn ($n) => $subjects[$n]['avg'] ?? null, $list), fn ($v) => $v !== null));
            if ($vals) $out[$area] = round(array_sum($vals) / count($vals), 1);
        }
        return $out;
    }

    private function detectALevel(array $subjects): ?string
    {
        $taken = array_map('strtolower', array_keys($subjects));
        foreach (self::combinations() as $code => $c) {
            if (collect($c['a_level'])->every(fn ($n) => in_array(strtolower($n), $taken, true))) return $code;
        }
        return null;
    }

    private function subjectPerformance(Student $s): array
    {
        $marks = Mark::with(['subject', 'exam'])->where('student_id', $s->id)->whereNotNull('mark')->get();
        if ($marks->isEmpty()) return [];

        $classAvg = Mark::whereIn('exam_id', $marks->pluck('exam_id')->unique())
            ->whereIn('subject_id', $marks->pluck('subject_id')->unique())
            ->when($s->class_id, fn ($q) => $q->where('class_id', $s->class_id))
            ->selectRaw('subject_id, AVG(mark) a')->groupBy('subject_id')->pluck('a', 'subject_id');

        $out = [];
        foreach ($marks->groupBy('subject_id') as $sid => $rows) {
            $name = $rows->first()->subject->name ?? "Subject {$sid}";
            $sorted = $rows->sortBy(fn ($m) => $m->exam->id ?? 0)->values();
            $avg = round($rows->avg('mark'), 1);
            $first = (float) $sorted->first()->mark; $last = (float) $sorted->last()->mark;
            $dir = $rows->count() > 1 ? ($last - $first >= 3 ? 'up' : ($first - $last >= 3 ? 'down' : 'flat')) : 'flat';
            $out[$name] = [
                'avg' => $avg,
                'class_avg' => isset($classAvg[$sid]) ? round((float) $classAvg[$sid], 1) : null,
                'grade' => $this->grade($avg),
                'trend' => $rows->count() > 1 ? sprintf('%.0f→%.0f (%s)', $first, $last, ['up' => 'improving', 'down' => 'falling', 'flat' => 'steady'][$dir]) : 'one exam only',
                'first' => $first, 'last' => $last, 'direction' => $dir,
                'exams' => $rows->count(),
            ];
        }
        uasort($out, fn ($a, $b) => $b['avg'] <=> $a['avg']);
        return $out;
    }

    private function aptitude(Student $s): ?array
    {
        $att = AptitudeAttempt::where('student_id', $s->id)->latest('id')->first();
        if (! $att) return null;

        $sections = [];
        foreach (AptitudeAnswer::with('question')->where('attempt_id', $att->id)->get() as $a) {
            $sec = $a->question->section ?? 'General';
            $sections[$sec]['got'] = ($sections[$sec]['got'] ?? 0) + (float) $a->obtained_marks;
            $sections[$sec]['max'] = ($sections[$sec]['max'] ?? 0) + (float) ($a->question->marks ?? 0);
        }
        foreach ($sections as $k => $v) $sections[$k]['pct'] = $v['max'] ? (int) round($v['got'] / $v['max'] * 100) : 0;
        $max = array_sum(array_column($sections, 'max'));

        return ['date' => $att->created_at?->format('d/m/Y'), 'total' => $att->total_score, 'max' => $max ?: '?',
            'pct' => $max ? (int) round($att->total_score / $max * 100) : '?', 'sections' => $sections];
    }
}
