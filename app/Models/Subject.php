<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Mark;
use App\Models\Student;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'teacher_id',
    ];

    /**
     * 🔹 Classes this subject belongs to (many-to-many)
     */
    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'subject_class',
            'subject_id',
            'class_id'
        )->withTimestamps();
    }

    /**
     * 🔹 Marks related to this subject
     */
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * 🔹 Teacher assigned to this subject
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * 🔹 Accessor: Get the teacher’s full name easily
     */
    public function getTeacherNameAttribute(): string
    {
        return $this->teacher
            ? "{$this->teacher->first_name} {$this->teacher->last_name}"
            : '—';
    }

    /**
     * 🔹 All students assigned to this subject (pivot 'withdrawn' available)
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'student_id')
                    ->withPivot('withdrawn')
                    ->withTimestamps();
    }

    /**
     * 🔹 Only students who are actively taking this subject (not withdrawn)
     */
    public function activeStudents()
    {
        return $this->students()->wherePivot('withdrawn', 0);
    }

    /**
     * 🔹 Optionally, a helper to get withdrawn students
     */
    public function withdrawnStudents()
    {
        return $this->students()->wherePivot('withdrawn', 1);
    }
}
