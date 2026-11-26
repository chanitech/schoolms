<?php

namespace App\Services;

use App\Models\AptitudeAnswer;
use App\Models\AptitudeAttempt;
use App\Models\Grade;
use App\Models\InterestInventory;
use App\Models\Mark;
use App\Models\Student;

/**
 * Evidence for subject-combination and career advice: per-subject
 * performance against the class, aptitude by section, interest-inventory
 * answers, and how well each Tanzanian A-level combination fits the
 * student's O-level results. The AI turns this into the written advice.
 */
class CareerGuidance
{
    /** A-level combination => [O-level subjects it builds on, careers it leads to, aptitude it leans on] */
    public const COMBINATIONS = [
        'PCM' => [['Physics', 'Chemistry', 'Basic Mathematics'], 'Engineering (civil, electrical, mechanical), architecture, computer science, ICT, actuarial science, aviation', 'Numerical'],
        'PCB' => [['Physics', 'Chemistry', 'Biology'], 'Medicine, dentistry, pharmacy, nursing, laboratory and biomedical sciences', 'Numerical'],
        'PGM' => [['Physics', 'Geography', 'Basic Mathematics'], 'Engineering, land surveying, meteorology, geology, mining, urban planning', 'Spatial'],
        'CBG' => [['Chemistry', 'Biology', 'Geography'], 'Agriculture, environmental science, veterinary, food science, wildlife and forestry', 'Spatial'],
        'EGM' => [['Basic Mathematics', 'Geography', 'Commerce'], 'Economics, statistics, finance, banking, planning, actuarial', 'Numerical'],
        'ECA' => [['Commerce', 'Book-keeping', 'Basic Mathematics'], 'Accounting (CPA), business administration, banking, procurement, tax', 'Numerical'],
        'HGE' => [['History', 'Geography', 'Basic Mathematics'], 'Law, economics, public administration, development studies, planning', 'Verbal'],
        'HGK' => [['History', 'Geography', 'Kiswahili'], 'Teaching, journalism, public administration, community development, tourism', 'Verbal'],
        'HGL' => [['History', 'Geography', 'English Language'], 'Law, international relations, journalism, tourism, diplomacy', 'Verbal'],
        'HKL' => [['History', 'Kiswahili', 'English Language'], 'Law, mass communication, languages, translation, teaching', 'Verbal'],
    ];

    private const CLUSTERS = [
        'Sciences'   => ['Physics', 'Chemistry', 'Biology'],
        'Mathematics'=> ['Basic Mathematics', 'Additional Mathematics'],
        'Languages'  => ['English Language', 'Kiswahili', 'French', 'Literature in English'],
        'Humanities' => ['History', 'Geography', 'Civics'],
        'Business'   => ['Commerce', 'Book-keeping'],
    ];

    public function __construct(private AIDataAccess $access) {}

    public function forStudent(string $query): array
    {
        $query = trim($query);
        if ($query === '') return ['_exact_summary' => 'Which student? Give a name or admission number.'];
        $s = Student::with('schoolClass')
            ->where(fn ($q) => $q->where('admission_no', $query)->orWhere('id', is_numeric($query) ? (int) $query : 0)
                ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$query}%"])
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]))
            ->first();
        if (! $s) return ['_exact_summary' => "No student found for '{$query}'."];

        $className = $s->schoolClass->name ?? 'Unknown class';
        $form = $this->formLevel($className);
        $L = ["STUDENT: {$s->first_name} {$s->last_name} (ID {$s->id}), {$className}" . ($form ? " — Form {$form}" : '')];

        $subjects = [];
        if ($this->access->canModel('Mark')) {
            $subjects = $this->subjectPerformance($s);
            if ($subjects) {
                $L[] = "\nSUBJECT PERFORMANCE (average of all exams; class average; trend first→latest exam; grade on the school's scale):";
                foreach ($subjects as $name => $p) {
                    $diff = $p['class_avg'] !== null ? sprintf('%+.1f vs class %.1f', $p['avg'] - $p['class_avg'], $p['class_avg']) : 'no class comparison';
                    $L[] = sprintf('• %s: %.1f (%s), grade %s, trend %s, %d exam(s)', $name, $p['avg'], $diff, $p['grade'], $p['trend'], $p['exams']);
                }
                $L[] = "\nSTRENGTH BY AREA (average of subjects taken):";
                foreach (self::CLUSTERS as $area => $list) {
                    $vals = array_values(array_filter(array_map(fn ($n) => $subjects[$n]['avg'] ?? null, $list), fn ($v) => $v !== null));
                    if ($vals) $L[] = sprintf('• %s: %.1f', $area, array_sum($vals) / count($vals));
                }
            } else {
                $L[] = 'SUBJECT PERFORMANCE: no marks recorded yet.';
            }
        } else {
            $L[] = 'SUBJECT PERFORMANCE: not available — this user\'s role cannot see marks.';
        }

        $aptitude = $this->access->canModel('AptitudeAttempt') ? $this->aptitude($s) : null;
        if ($aptitude) {
            $L[] = "\nAPTITUDE TEST ({$aptitude['date']}): total {$aptitude['total']} of {$aptitude['max']} (" . $aptitude['pct'] . '%)';
            foreach ($aptitude['sections'] as $sec => $v) $L[] = "• {$sec}: {$v['got']} of {$v['max']} ({$v['pct']}%)";
        } elseif (! $this->access->canModel('AptitudeAttempt')) {
            $L[] = "\nAPTITUDE: not available to this user's role.";
        } else {
            $L[] = "\nAPTITUDE: no test taken yet.";
        }

        if ($this->access->canModel('InterestInventory')) {
            $inv = InterestInventory::where('student_id', $s->id)->latest('date')->first();
            if ($inv) {
                $labels = [1 => 'Most interesting subject', 2 => 'Most challenging subject', 3 => 'Enjoys most at school', 6 => 'Activities outside school',
                    7 => 'Describes self as', 8 => 'Careers of interest', 9 => 'Ideal job', 17 => 'Would like to learn more about'];
                $L[] = "\nINTEREST INVENTORY (" . $inv->date?->format('d/m/Y') . '):';
                foreach ($labels as $i => $label) if (filled($inv->{"q{$i}"})) $L[] = "• {$label}: " . trim($inv->{"q{$i}"});
            } else {
                $L[] = "\nINTEREST INVENTORY: not completed yet.";
            }
        }

        if ($subjects) {
            $L[] = "\nA-LEVEL COMBINATION FIT (average of the O-level subjects each combination builds on; ✓ = credit C or better in all three):";
            $fits = [];
            foreach (self::COMBINATIONS as $code => [$needs, $careers, $leans]) {
                $have = array_values(array_filter($needs, fn ($n) => isset($subjects[$n])));
                $missing = array_diff($needs, $have);
                $score = $have ? array_sum(array_map(fn ($n) => $subjects[$n]['avg'], $have)) / count($have) : 0;
                $credit = ! $missing && collect($needs)->every(fn ($n) => $subjects[$n]['avg'] >= $this->creditMark());
                $boost = $aptitude && isset($aptitude['sections'][$leans]) ? $aptitude['sections'][$leans]['pct'] : null;
                $fits[$code] = compact('needs', 'careers', 'leans', 'missing', 'score', 'credit', 'boost');
            }
            uasort($fits, fn ($a, $b) => (count($a['missing']) <=> count($b['missing'])) ?: ($b['score'] <=> $a['score']));
            foreach ($fits as $code => $f) {
                $line = sprintf('• %s (%s): %.1f%s', $code, implode(', ', $f['needs']), $f['score'], $f['credit'] ? ' ✓' : '');
                if ($f['missing']) $line .= ' — no record for ' . implode(', ', $f['missing']);
                if ($f['boost'] !== null) $line .= " — leans on {$f['leans']} aptitude ({$f['boost']}%)";
                $L[] = $line . ". Careers: {$f['careers']}";
            }
        }

        $L[] = "\nCONTEXT: In Tanzania students sit CSEE (NECTA) after Form IV and choose an A-level combination for Form V–VI; "
            . 'selection normally needs at least a credit (C) in the subjects the combination builds on. '
            . 'On this school\'s grading scale a credit (C) starts at ' . $this->creditMark() . ' marks — use this number for targets. '
            . ($form && $form <= 2 ? 'This student is in Form ' . $form . ', so advice should be about which O-level direction (sciences, business or arts) to strengthen for Form III and which combinations to aim for, not a final choice.' : '')
            . ($form && $form >= 5 ? 'This student is already in A-level; focus on university courses and careers that fit their combination and grades.' : '');

        return ['guidance_evidence' => implode("\n", $L),
            'instructions' => 'Write career guidance from this evidence: (1) top 2–3 combinations with reasons citing the grades, aptitude and interests; '
                . '(2) careers each opens; (3) the subjects to improve and by how much to reach a credit; (4) one line saying it is advice to discuss with the student, parents and career teacher. '
                . 'Do not invent marks. If evidence is thin (few exams, no aptitude or interests), say what to collect next.'];
    }

    private function formLevel(string $className): ?int
    {
        if (! preg_match('/form\s*(VI|IV|V|III|II|I|[1-6])\b/i', $className, $m)) return null;
        return ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6][strtoupper($m[1])] ?? (int) $m[1];
    }

    private function creditMark(): float
    {
        // Lowest mark that still earns a C (credit) on this school's scale; NECTA default 45
        return (float) (Grade::where('name', 'C')->value('min_mark') ?? 45);
    }

    private function subjectPerformance(Student $s): array
    {
        $marks = Mark::with(['subject', 'exam'])->where('student_id', $s->id)->whereNotNull('mark')->get();
        if ($marks->isEmpty()) return [];

        $grades = Grade::orderByDesc('min_mark')->get(['name', 'min_mark']);
        $grade = fn ($v) => optional($grades->first(fn ($g) => $v >= $g->min_mark))->name ?? '-';

        // class averages per subject over the same exams
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
            $out[$name] = [
                'avg' => $avg,
                'class_avg' => isset($classAvg[$sid]) ? round((float) $classAvg[$sid], 1) : null,
                'grade' => $grade($avg),
                'trend' => $rows->count() > 1 ? sprintf('%.0f→%.0f (%s)', $first, $last, $last - $first >= 3 ? 'improving' : ($first - $last >= 3 ? 'falling' : 'steady')) : 'one exam only',
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

        $answers = AptitudeAnswer::with('question')->where('attempt_id', $att->id)->get();
        $sections = [];
        foreach ($answers as $a) {
            $sec = $a->question->section ?? 'General';
            $sections[$sec]['got'] = ($sections[$sec]['got'] ?? 0) + (float) $a->obtained_marks;
            $sections[$sec]['max'] = ($sections[$sec]['max'] ?? 0) + (float) ($a->question->marks ?? 0);
        }
        foreach ($sections as $k => $v) $sections[$k]['pct'] = $v['max'] ? round($v['got'] / $v['max'] * 100) : 0;
        $max = array_sum(array_column($sections, 'max'));

        return ['date' => $att->created_at?->format('d/m/Y'), 'total' => $att->total_score, 'max' => $max ?: '?',
            'pct' => $max ? round($att->total_score / $max * 100) : '?', 'sections' => $sections];
    }
}
