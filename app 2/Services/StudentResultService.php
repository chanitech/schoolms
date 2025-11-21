<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Division;

class StudentResultService
{
    /**
     * Calculate GPA & Division (TZ O-Level system)
     *
     * @param array $marks ['Math' => 75, 'Biology' => 60, ...]
     * @return array ['gpa'=>1.86, 'total_points'=>13, 'division'=>'I']
     */
    public static function calculateGpaAndDivision(array $marks)
{
    $points = [];

    // Convert marks to points
    foreach ($marks as $subject => $mark) {
        $grade = Grade::gradeForMark($mark);
        $points[] = $grade ? $grade->point : 5; // fail point if missing
    }

    // Best 7 subjects (lowest points)
    sort($points); // lowest first
    $bestPoints = array_slice($points, 0, 7);

    $totalPoints = array_sum($bestPoints);
    $countBest = count($bestPoints);

    $gpa = $countBest > 0 ? $totalPoints / $countBest : 0;

    $division = Division::where('min_points', '<=', $totalPoints)
                        ->where('max_points', '>=', $totalPoints)
                        ->first();

    return [
        'gpa' => round($gpa, 2),
        'total_points' => $totalPoints,
        'division' => $division ? $division->name : 'Unknown',
    ];
}

}
