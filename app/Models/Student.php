<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Dormitory;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Mark;

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
        'dormitory_id',
        'academic_session_id',
        'admission_date',
        'status',
        'address',
        'phone',
        'email',
    ];

    // Relationships
    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function marksForExam($examId)
    {
        return $this->marks()->where('exam_id', $examId);
    }

    public function marksForSession($sessionId)
    {
        return $this->marks()->where('academic_session_id', $sessionId);
    }
}
