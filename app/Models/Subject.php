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
        'type',          // e.g. 'core' or 'elective'
        'teacher_id',
        'department_id',
    ];

    /* ============================
     | 🔹 RELATIONSHIPS
     ============================ */

    // Each subject belongs to one department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Classes this subject is offered to
    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'subject_class',
            'subject_id',
            'class_id'
        )->withTimestamps();
    }

    // Marks recorded under this subject
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // Teacher assigned to this subject
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Students taking this subject
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'student_id')
                    ->withPivot('withdrawn')
                    ->withTimestamps();
    }

    // Active students (not withdrawn)
    public function activeStudents()
    {
        return $this->students()->wherePivot('withdrawn', 0);
    }

    // Withdrawn students
    public function withdrawnStudents()
    {
        return $this->students()->wherePivot('withdrawn', 1);
    }

    /* ============================
     | 🔹 ACCESSORS
     ============================ */

    // Teacher’s full name
    public function getTeacherNameAttribute(): string
    {
        return $this->teacher
            ? trim("{$this->teacher->first_name} {$this->teacher->last_name}")
            : '—';
    }

    /* ============================
     | 🔹 SCOPES
     ============================ */

    // Filter by department
    public function scopeOfDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Filter by type (core/elective)
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /* ============================
     | 🔹 HELPERS
     ============================ */

    // Get average GPA or marks across all students in this subject
    public function averageGpa()
    {
        return $this->marks()->avg('gpa');
    }

    public function averageScore()
    {
        return $this->marks()->avg('score');
    }
}
