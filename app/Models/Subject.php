<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{
    SchoolClass,
    User,
    Mark,
    Student,
    Department
};

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',             // core / elective
        'department_id',    // subject belongs to a department
    ];

    /* ============================
     | 🔹 RELATIONSHIPS
     ============================ */

    // Each subject belongs to one department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classes()
{
    return $this->belongsToMany(
        SchoolClass::class,
        'subject_class',
        'subject_id',
        'class_id'
    )->withPivot('teacher_id')->withTimestamps();
}



    // Marks recorded under this subject
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // Students taking this subject
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'student_id')
                    ->withPivot('withdrawn')
                    ->withTimestamps();
    }

    public function activeStudents()
    {
        return $this->students()->wherePivot('withdrawn', 0);
    }

    public function withdrawnStudents()
    {
        return $this->students()->wherePivot('withdrawn', 1);
    }

    /* ============================
     | 🔹 ACCESSORS
     ============================ */

    // Default teacher name (if needed for UI)
    public function getTeacherNameAttribute(): string
    {
        return '—';  // teacher is now class-specific so default is blank
    }

    /* ============================
     | 🔹 SCOPES
     ============================ */

    public function scopeOfDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /* ============================
     | 🔹 HELPERS
     ============================ */

    public function averageGpa()
    {
        return $this->marks()->avg('gpa');
    }

    public function averageScore()
    {
        return $this->marks()->avg('score');
    }

    /**
     * Get the assigned teacher for a specific class
     */
    public function teacherForClass($classId)
    {
        $record = $this->classes()
                       ->where('class_id', $classId)
                       ->first();

        return $record?->pivot?->teacher_id;  // teacher_id or null
    }
}
