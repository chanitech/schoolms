<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Division;
use Illuminate\Support\Collection;

class StudentResultService
{
    /**
     * Look up division name from the divisions DB table.
     * Returns '0' (unclassified) if no matching range found — no hardcoding.
     */
    private static function divisionForPoints(int $totalPoints): string
    {
        $division = Division::where('min_points', '<=', $totalPoints)
                            ->where('max_points', '>=', $totalPoints)
                            ->first();

        return $division ? $division->name : '0';
    }

    /**
     * Pick the best 7 subjects and compute all result metrics.
     *
     * Best 7 = lowest grade points (A=1 best, F=5 worst — NECTA).
     * Core subjects fill first, then electives for remaining slots.
     *
     * average_mark and GPA are derived from the SAME best-7 subjects,
     * so a student with fewer total_points will ALWAYS have a higher
     * or equal average_mark — rank by points and rank by average are consistent.
     *
     * @param  Collection $subjectsData  Each item must have: type, mark, point
     * @return array [
     *     'bestSubjects'  => Collection,
     *     'total_points'  => int,      (primary rank key — lower = better)
     *     'average_mark'  => float,    (display — from best 7 only)
     *     'gpa'           => float,    (avg point of best 7)
     *     'division'      => string,   (from DB Division table)
     * ]
     */
    public static function computeFromSubjects(Collection $subjectsData): array
    {
        $withMark  = $subjectsData->filter(fn($s) => $s['mark'] !== null && $s['mark'] !== '');
        $core      = $withMark->where('type', 'core')->sortBy('point')->values();
        $electives = $withMark->where('type', 'elective')->sortBy('point')->values();

        $coreSlots     = min(7, $core->count());
        $electiveSlots = min(7 - $coreSlots, $electives->count());
        $bestSubjects  = $core->take($coreSlots)->merge($electives->take($electiveSlots));

        $count       = $bestSubjects->count();
        $totalPoints = (int) $bestSubjects->sum('point');
        $averageMark = $count > 0 ? round($bestSubjects->sum('mark') / $count, 2) : 0.0;
        $gpa         = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

        return [
            'bestSubjects' => $bestSubjects,
            'total_points' => $totalPoints,
            'average_mark' => $averageMark,
            'gpa'          => $gpa,
            'division'     => self::divisionForPoints($totalPoints),
        ];
    }

    /**
     * Compute GPA and division from a known total_points value.
     * Use when you already have total_points and don't need to reprocess subjects.
     *
     * @param  int  $totalPoints
     * @param  int  $subjectCount  Number of subjects used (for GPA average)
     * @return array ['gpa' => float, 'total_points' => int, 'division' => string]
     */
    public static function calculateFromPoints(int $totalPoints, int $subjectCount = 7): array
    {
        $subjectCount = max($subjectCount, 1);

        return [
            'gpa'          => round($totalPoints / $subjectCount, 2),
            'total_points' => $totalPoints,
            'division'     => self::divisionForPoints($totalPoints),
        ];
    }

    /**
     * Assign rank numbers to an already-sorted collection.
     *
     * Sort the collection by total_points ASC before calling this,
     * then re-sort by average_mark DESC for display — ranks stay correct.
     *
     * Ties: students with equal total_points share the same rank.
     * The next distinct rank skips the tied positions (standard competition ranking).
     *
     * @param  Collection $studentsData  Each item must have 'total_points'
     * @return Collection  Same items with 'position' key added
     */
    public static function assignRanks(Collection $studentsData): Collection
    {
        // Always rank from lowest total_points (best) to highest (worst)
        $sorted     = $studentsData->sortBy('total_points')->values();
        $position   = 0;
        $skip       = 1;
        $prevPoints = null;

        return $sorted->map(function ($item) use (&$position, &$skip, &$prevPoints) {
            $pts = $item['total_points'];

            if ($prevPoints === null) {
                $position = 1;
                $skip     = 1;
            } elseif ($pts === $prevPoints) {
                $skip++;                        // tied — same rank, next rank skips
            } else {
                $position += $skip;
                $skip      = 1;
            }

            $item['position'] = $position;
            $prevPoints       = $pts;

            return $item;
        });
    }

    /**
     * Legacy: accepts raw mark values, converts to points via Grade table,
     * picks best 7, returns result. Prefer computeFromSubjects() when you
     * already have grade points to avoid double-conversion.
     *
     * @param  array $marks  e.g. [75, 60, 82, ...]
     * @return array ['gpa' => float, 'total_points' => int, 'division' => string]
     */
    public static function calculateGpaAndDivision(array $marks): array
    {
        $grades = Grade::all();
        $points = [];

        foreach ($marks as $mark) {
            if (!is_numeric($mark)) continue;
            $grade    = $grades->firstWhere(fn($g) => $mark >= $g->min_mark && $mark <= $g->max_mark);
            $points[] = $grade ? (int) $grade->point : 5;
        }

        if (empty($points)) {
            return ['gpa' => 0, 'total_points' => 0, 'division' => '0'];
        }

        sort($points);                       // ascending: lowest = best subjects
        $bestPoints  = array_slice($points, 0, 7);
        $totalPoints = array_sum($bestPoints);

        return self::calculateFromPoints($totalPoints, count($bestPoints));
    }
}