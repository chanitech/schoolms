<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{
    Guardian,
    SchoolClass,
    Dormitory,
    AcademicSession,
    Enrollment,
    Mark,
    Subject,
    Department
};

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'national_id',
        'photo',
        'guardian_id',
        'class_id',
        'department_id',
        'dormitory_id',
        'academic_session_id',
        'admission_date',
        'status',
        'address',
        'phone',
        'email',
    ];

    /* ============================
     | 🔹 RELATIONSHIPS
     ============================ */

    // Guardian
    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    // School Class
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Add this alias for backward compatibility
public function class()
{
    return $this->schoolClass();
}

    // Dormitory
    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    // Academic Session
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    // Department (NEW)
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Enrollments
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Marks
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // Marks filtered by exam
    public function marksForExam($examId)
    {
        return $this->marks()->where('exam_id', $examId);
    }

    // Marks filtered by session
    public function marksForSession($sessionId)
    {
        return $this->marks()->where('academic_session_id', $sessionId);
    }

    // Subjects (many-to-many)
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id')
                    ->withPivot('withdrawn')
                    ->withTimestamps();
    }

    // Active Subjects (not withdrawn)
    public function activeSubjects()
    {
        return $this->subjects()->wherePivot('withdrawn', 0);
    }

    // Withdrawn Subjects
    public function withdrawnSubjects()
    {
        return $this->subjects()->wherePivot('withdrawn', 1);
    }

    /* ============================
     | 🔹 ACCESSORS
     ============================ */

    public function getFullNameAttribute()
    {
        $names = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $names);
    }

    /* ============================
     | 🔹 SCOPES
     ============================ */

    // Filter students by department
    public function scopeOfDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Filter students by active status
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
 * Student results
 */
public function results()
{
    return $this->marks();
}

// Add inside Student model
public function getNameAttribute()
{
    return $this->full_name;
}


}
