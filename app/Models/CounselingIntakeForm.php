<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingIntakeForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id','gender','age','stream','education_program','g_performance','living_situation',
        'father_name','father_address','father_occupation','father_age','father_phone',
        'guardian_name','guardian_relationship',
        'mother_name','mother_address','mother_occupation','mother_age','mother_phone',
        'parents_relationship','siblings_brothers','siblings_sisters','birth_order',
        'referred_by','health_problems','previous_counseling','reason_for_counseling','chief_complaint',
        'understanding_of_services','counseling_type'
    ];

    protected $casts = [
        'counseling_type' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

