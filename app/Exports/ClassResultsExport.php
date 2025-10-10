<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassResultsExport implements FromArray, WithHeadings, WithMapping
{
    protected $studentsData;
    protected $subjects;

    public function __construct(array $studentsData, $subjects = [])
    {
        $this->studentsData = $studentsData;
        $this->subjects = $subjects; // Collection of subjects
    }

    /**
     * Prepare array of data to export
     */
    public function array(): array
    {
        return $this->studentsData;
    }

    /**
     * Map each row (student) for export
     */
    public function map($studentData): array
    {
        $row = [
            $studentData['position'],
            $studentData['student']->first_name . ' ' . $studentData['student']->last_name,
        ];

        // Add marks for each subject dynamically
        foreach ($this->subjects as $subject) {
            $subjectInfo = $studentData['subjectsData'][$subject->name] ?? ['mark' => null, 'grade' => '-', 'point' => 0];
            $row[] = $subjectInfo['mark'];
            $row[] = $subjectInfo['grade'];
        }

        // Append totals
        $row[] = $studentData['total_points'];
        $row[] = number_format($studentData['gpa'], 2);
        $row[] = $studentData['division'];

        return $row;
    }

    /**
     * Define headings dynamically
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
