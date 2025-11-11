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
        'academic_session_id', // <- add this
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


// Inside Mark.php

/**
 * 🔹 Access the department of the mark via the subject
 */
public function department()
{
    return $this->hasOneThrough(
        \App\Models\Department::class, // final model
        \App\Models\Subject::class,    // intermediate model
        'id',        // Foreign key on Subject (subject.id)
        'id',        // Foreign key on Department (department.id)
        'subject_id',// Local key on Mark (mark.subject_id)
        'department_id' // Local key on Subject (subject.department_id)
    );
}

/**
 * 🔹 Scope to filter marks by department
 */
public function scopeOfDepartment($query, $departmentId)
{
    return $query->whereHas('subject', function($q) use ($departmentId) {
        $q->where('department_id', $departmentId);
    });
}



    
}
