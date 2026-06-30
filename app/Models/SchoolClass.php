<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Concerns\BelongsToSchool;

class SchoolClass extends Model
{
    use BelongsToSchool;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'level',
        'section',
        'capacity',
        'class_teacher_id',
    ];

    // Relation: Class -> Students
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    // Relation: Class -> Class Teacher
    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }

    // Relation: Class -> Subjects (with pivot teacher)
    public function subjects()
{
    return $this->belongsToMany(
        Subject::class,
        'subject_class',
        'class_id',
        'subject_id'
    )->withPivot('teacher_id')->withTimestamps();
}

}
