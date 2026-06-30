<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\ExamQuestion;
use App\Models\Exam;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionMarksTemplateExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $classId;
    protected $sessionId;
    protected $examId;
    protected $subjectId;
    protected $questions;

    public function __construct(int $classId, int $sessionId, int $examId, int $subjectId)
    {
        $this->classId   = $classId;
        $this->sessionId = $sessionId;
        $this->examId    = $examId;
        $this->subjectId = $subjectId;
        $this->questions = ExamQuestion::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->orderBy('question_no')
            ->get();
    }

    public function array(): array
    {
        $exam    = Exam::find($this->examId);
        $subject = Subject::find($this->subjectId);

        // Row 1: info header
        $infoRow = ['Exam: ' . ($exam->name ?? ''), 'Subject: ' . ($subject->name ?? ''), '', ''];
        foreach ($this->questions as $q) {
            $infoRow[] = '';
        }

        // Row 2: column headers
        $headingRow = ['admission_no', 'student_name', 'student_id'];
        foreach ($this->questions as $q) {
            $headingRow[] = 'Q' . $q->question_no;
        }

        // Row 3: max marks row (for reference)
        $maxRow = ['', '', 'MAX MARKS →'];
        foreach ($this->questions as $q) {
            $maxRow[] = (float) $q->max_marks;
        }

        // Student rows
        $students = Enrollment::with('student')
            ->where('class_id', $this->classId)
            ->where('academic_session_id', $this->sessionId)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn($e) => $e->student->first_name . ' ' . $e->student->last_name)
            ->values();

        $rows = [$infoRow, $headingRow, $maxRow];

        foreach ($students as $enrollment) {
            $s   = $enrollment->student;
            $row = [
                $s->admission_no,
                trim($s->first_name . ' ' . $s->last_name),
                $s->id,
            ];
            foreach ($this->questions as $q) {
                $row[] = '';  // blank score column
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'C';
        $colIndex = 3 + $this->questions->count();
        $lastCol  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9E1F2']]],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BDD7EE']]],
            3 => ['font' => ['italic' => true, 'color' => ['rgb' => '7F7F7F']]],
        ];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 18, 'B' => 28, 'C' => 14];
        $index  = 4;
        foreach ($this->questions as $q) {
            $col           = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
            $widths[$col]  = 10;
            $index++;
        }
        return $widths;
    }
}
