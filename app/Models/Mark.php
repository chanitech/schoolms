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


    
}
