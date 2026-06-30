<?php

namespace App\Imports;

use App\Models\Enrollment;
use App\Models\ExamQuestion;
use App\Models\Mark;
use App\Models\MarkQuestionScore;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class QuestionMarksImport implements ToCollection
{
    private int $classId;
    private int $sessionId;
    private int $examId;
    private int $subjectId;
    private int $successCount = 0;
    private array $errors     = [];

    public function __construct(int $classId, int $sessionId, int $examId, int $subjectId)
    {
        $this->classId   = $classId;
        $this->sessionId = $sessionId;
        $this->examId    = $examId;
        $this->subjectId = $subjectId;
    }

    public function collection(Collection $rows)
    {
        // Load questions for this exam + subject
        $questions = ExamQuestion::where('exam_id', $this->examId)
            ->where('subject_id', $this->subjectId)
            ->orderBy('question_no')
            ->get()
            ->keyBy('question_no');

        if ($questions->isEmpty()) {
            $this->errors[] = 'No questions defined for this exam and subject.';
            return;
        }

        // Valid enrolled student IDs
        $validStudentIds = Student::whereHas('enrollments', fn($q) =>
            $q->where('class_id', $this->classId)
              ->where('academic_session_id', $this->sessionId)
              ->where('status', 'active')
        )->pluck('id')->flip();

        // Find the heading row (row with 'admission_no' or 'student_id')
        $headingRowIndex = null;
        $headers         = [];
        foreach ($rows as $index => $row) {
            $cell = strtolower(trim((string) $row[0]));
            if ($cell === 'admission_no') {
                $headingRowIndex = $index;
                $headers         = $row->toArray();
                break;
            }
        }

        if ($headingRowIndex === null) {
            $this->errors[] = 'Could not find heading row. Make sure the template has not been modified.';
            return;
        }

        // Map column index → question_no (Q1 → 1, Q2 → 2, …)
        $qColMap = [];   // colIndex => question_no
        foreach ($headers as $colIdx => $header) {
            if (preg_match('/^Q(\d+)$/i', trim((string) $header), $m)) {
                $qColMap[$colIdx] = (int) $m[1];
            }
        }

        if (empty($qColMap)) {
            $this->errors[] = 'No question columns (Q1, Q2, …) found in template.';
            return;
        }

        // Determine column positions
        $admissionCol  = 0;
        $studentIdCol  = 2;

        DB::transaction(function () use ($rows, $headingRowIndex, $questions, $qColMap, $validStudentIds, $admissionCol, $studentIdCol) {
            foreach ($rows as $index => $row) {
                // Skip header rows and info rows
                if ($index <= $headingRowIndex) continue;

                $admissionNo = trim((string) ($row[$admissionCol] ?? ''));
                $studentId   = (int) ($row[$studentIdCol] ?? 0);

                // Skip empty rows
                if (!$admissionNo && !$studentId) continue;

                // Resolve student
                if (!$studentId && $admissionNo) {
                    $student   = Student::where('admission_no', $admissionNo)->first();
                    $studentId = $student?->id ?? 0;
                }

                if (!$studentId || !isset($validStudentIds[$studentId])) {
                    $this->errors[] = "Row " . ($index + 1) . ": student not found or not enrolled (admission_no: $admissionNo).";
                    continue;
                }

                // Collect scores per question
                $scores   = [];
                $rawTotal = 0;
                $totalMax = 0;
                $hasAny   = false;
                $rowValid = true;

                foreach ($qColMap as $colIdx => $questionNo) {
                    $question = $questions->get($questionNo);
                    if (!$question) continue;

                    $val = $row[$colIdx] ?? null;
                    if ($val === null || $val === '') continue;  // blank = skip

                    if (!is_numeric($val)) {
                        $this->errors[] = "Row " . ($index + 1) . " Q{$questionNo}: non-numeric score '$val'.";
                        $rowValid = false;
                        break;
                    }

                    $score = (float) $val;
                    $max   = (float) $question->max_marks;

                    if ($score < 0 || $score > $max) {
                        $this->errors[] = "Row " . ($index + 1) . " Q{$questionNo}: score $score exceeds max $max.";
                        $rowValid = false;
                        break;
                    }

                    $scores[$question->id] = $score;
                    $rawTotal += $score;
                    $hasAny    = true;
                }

                if (!$rowValid || !$hasAny) continue;

                // Compute total max for percentage
                $totalMax = $questions->sum('max_marks');
                $pct      = $totalMax > 0 ? round(($rawTotal / $totalMax) * 100, 4) : 0;

                // Save mark record
                $mark = Mark::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $this->subjectId,
                        'exam_id'    => $this->examId,
                    ],
                    [
                        'mark'                => $pct,
                        'class_id'            => $this->classId,
                        'academic_session_id' => $this->sessionId,
                        'grade_id'            => null,
                    ]
                );

                // Save per-question scores
                foreach ($scores as $questionId => $score) {
                    MarkQuestionScore::updateOrCreate(
                        ['mark_id' => $mark->id, 'exam_question_id' => $questionId],
                        ['score'   => $score]
                    );
                }

                $this->successCount++;
            }
        });
    }

    public function getSuccessCount(): int { return $this->successCount; }
    public function getErrors(): array     { return $this->errors; }
}
