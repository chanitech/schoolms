<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Mark;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class MarksImport implements ToCollection, WithHeadingRow
{
    private $classId;
    private $sessionId;
    private $subjectId;
    private $examId;
    private $successCount = 0;
    private $errors = [];
    private $warnings = [];

    public function __construct($classId, $sessionId, $subjectId, $examId)
    {
        $this->classId = $classId;
        $this->sessionId = $sessionId;
        $this->subjectId = $subjectId;
        $this->examId = $examId;
    }

    public function collection(Collection $rows)
    {
        // Get valid student IDs
        $validStudentIds = Student::whereHas('enrollments', function ($q) {
            $q->where('class_id', $this->classId)
              ->where('academic_session_id', $this->sessionId)
              ->where('status', 'active');
        })->pluck('id')->toArray();

        // Find the mark column (case‑insensitive)
        $firstRow = $rows->first();
        $markColumn = null;
        foreach ($firstRow->keys() as $key) {
            if (strtolower($key) === 'mark') {
                $markColumn = $key;
                break;
            }
        }

        if (!$markColumn) {
            $this->errors[] = 'Excel file must contain a column named "mark" (case‑insensitive)';
            return;
        }

        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;
            $admissionNo = $row['admission_no'] ?? null;
            $mark = $row[$markColumn] ?? null;

            // Skip rows where mark is empty
            if ($mark === null || $mark === '') {
                $this->warnings[] = "Skipped row for student ID $studentId: mark is empty";
                continue;
            }

            // Find student by ID or admission number
            if (!$studentId && $admissionNo) {
                $student = Student::where('admission_no', $admissionNo)->first();
                $studentId = $student->id ?? null;
            }

            if (!$studentId || !in_array($studentId, $validStudentIds)) {
                $this->errors[] = "Student ID $studentId (admission_no $admissionNo) not found in selected class/session";
                continue;
            }

            if (!is_numeric($mark) || $mark < 0 || $mark > 100) {
                $this->errors[] = "Invalid mark ($mark) for student $studentId (must be numeric 0-100)";
                continue;
            }

            $grade = Grade::where('min_mark', '<=', $mark)->where('max_mark', '>=', $mark)->first();

            // Update or create mark
            Mark::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $this->subjectId,
                    'exam_id'    => $this->examId,
                ],
                [
                    'mark'                 => $mark,
                    'class_id'             => $this->classId,
                    'academic_session_id'  => $this->sessionId,
                    'grade_id'             => $grade?->id,
                ]
            );

            $this->successCount++;
        }
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }
}