<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Grade;

class Mark extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'subject_id',
        'exam_id',
        'academic_session_id',
        'class_id', 
        'mark',
        'grade_id',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    /**
     * Access the department of the mark via the subject
     */
    public function department()
    {
        return $this->hasOneThrough(
            \App\Models\Department::class, 
            \App\Models\Subject::class,    
            'id',        // Foreign key on Subject
            'id',        // Foreign key on Department
            'subject_id',// Local key on Mark
            'department_id' 
        );
    }

    /**
     * Scope to filter marks by department
     */
    public function scopeOfDepartment($query, $departmentId)
    {
        return $query->whereHas('subject', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Scope to filter marks by teacher assignment using pivot table
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->whereHas('subject.classes', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        });
    }

    /**
     * Check if a student is withdrawn from this subject
     */
    public function isStudentWithdrawn($studentId)
    {
        return $this->subject->students()
                    ->where('student_id', $studentId)
                    ->wherePivot('withdrawn', 1)
                    ->exists();
    }
}
