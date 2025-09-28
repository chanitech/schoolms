<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Division;

class StudentResultService
{
    /**
     * Calculate GPA and Division for a student
     * 
     * @param array $marks Array of subjects and marks, e.g. ['Math' => 75, 'Biology' => 60]
     * @return array ['gpa' => 1.86, 'total_points' => 13, 'division' => 'I']
     */
    public static function calculateGpaAndDivision(array $marks)
    {
        // Step 1: Convert marks to points
        $points = [];
        foreach ($marks as $subject => $mark) {
            $grade = Grade::gradeForMark($mark);
            $points[] = $grade ? $grade->point : 5; // if no grade found, assign fail point
        }

        // Step 2: Take best 7 subjects (lowest points)
        sort($points); // ascending order, lower is better
        $points = array_slice($points, 0, 7);

        // Step 3: Sum points
        $totalPoints = array_sum($points);

        // Step 4: Calculate GPA
        $gpa = $totalPoints / count($points);

        // Step 5: Find Division
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
