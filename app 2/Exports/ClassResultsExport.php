<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class ClassResultsExport implements FromArray, WithHeadings, WithMapping
{
    protected $studentsData;
    protected $subjects;

    public function __construct(array $studentsData, $subjects = [])
    {
        $this->studentsData = $this->assignPositions($studentsData);
        $this->subjects = $subjects;
    }

    /**
     * Assign positions based on total_points (handles ties)
     */
    protected function assignPositions(array $students): array
    {
        $students = collect($students)->sortByDesc('total_points')->values();
        $position = 1;
        $prevPoints = null;

        foreach ($students as $i => &$student) {
            if ($prevPoints !== null && $student['total_points'] == $prevPoints) {
                $student['position'] = $students[$i - 1]['position'];
            } else {
                $student['position'] = $position;
            }
            $prevPoints = $student['total_points'];
            $position++;
        }

        return $students->toArray();
    }

    /**
     * Prepare array for Excel export
     */
    public function array(): array
    {
        return $this->studentsData;
    }

    /**
     * Map each student for Excel
     */
    public function map($studentData): array
    {
        // Key subjectsData by name for easier access
        $subjectsData = collect($studentData['subjectsData'])->keyBy('name')->toArray();

        $row = [
            $studentData['position'],
            $studentData['student']->first_name . ' ' . $studentData['student']->last_name,
        ];

        foreach ($this->subjects as $subject) {
            $subjectInfo = $subjectsData[$subject->name] ?? ['mark' => null, 'grade' => '-', 'point' => 0];
            $row[] = $subjectInfo['mark'];
            $row[] = $subjectInfo['grade'];
        }

        $row[] = $studentData['total_points'];
        $row[] = number_format($studentData['gpa'], 2);
        $row[] = $studentData['division'];

        return $row;
    }

    /**
     * Headings for Excel
     */
    public function headings(): array
    {
        $headings = ['Position', 'Student'];

        foreach ($this->subjects as $subject) {
            $headings[] = $subject->name . ' Mark';
            $headings[] = $subject->name . ' Grade';
        }

        $headings[] = 'Total Points';
        $headings[] = 'GPA';
        $headings[] = 'Division';

        return $headings;
    }
}
