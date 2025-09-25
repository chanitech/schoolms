<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    // Relationships (to be completed when other models exist)
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
}
