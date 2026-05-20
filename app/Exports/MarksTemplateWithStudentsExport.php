<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class MarksTemplateWithStudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $classId;
    protected $sessionId;
    protected $examId;
    protected $subjectId;
    protected $departmentId;

    public function __construct(Request $request)
    {
        $this->classId    = $request->input('class_id');
        $this->sessionId  = $request->input('academic_session_id');
        $this->examId     = $request->input('exam_id');
        $this->subjectId  = $request->input('subject_id');
        $this->departmentId = $request->input('department_id');
    }

    public function collection()
    {
        // Get active enrollments for the selected class & session
        $enrollments = Enrollment::with('student')
            ->where('class_id', $this->classId)
            ->where('academic_session_id', $this->sessionId)
            ->where('status', 'active')
            ->get();

        // Optional: if a subject is selected, we can also show its name in the template
        $subject = $this->subjectId ? Subject::find($this->subjectId) : null;

        $students = $enrollments->map(function ($enrollment) use ($subject) {
            return (object) [
                'admission_no'    => $enrollment->student->admission_no,
                'student_name'    => $enrollment->student->full_name,
                'class'           => $enrollment->class->name,
                'academic_session'=> $enrollment->academicSession->name,
                'subject_name'    => $subject ? $subject->name : 'N/A',
                'student_id'      => $enrollment->student->id,   // hidden column for import
            ];
        });

        // Sort students alphabetically by student name (full_name)
        $students = $students->sortBy('student_name')->values();

        return $students;
    }

    public function headings(): array
    {
        return [
            'Admission No',
            'Student Name',
            'Class',
            'Academic Session',
            'Subject',
            'Student ID (do not edit)',
            'Mark'
        ];
    }

    public function map($row): array
    {
        return [
            $row->admission_no,
            $row->student_name,
            $row->class,
            $row->academic_session,
            $row->subject_name,
            $row->student_id,
            '',   // blank mark column
        ];
    }
}