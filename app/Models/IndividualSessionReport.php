<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndividualSessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'user_id',
        'date',
        'time',
        'session_number',
        'presenting_problem',
        'work_done',
        'assessment_progress',
        'intervention_plan',
        'follow_up',
        'biopsychosocial_formulation',
    ];

    protected $casts = [
    'biopsychosocial_formulation' => 'array',
    'date' => 'datetime',       // ensures $report->date is a Carbon instance
    'created_at' => 'datetime', // optional, for consistency
    'updated_at' => 'datetime', // optional, for consistency
];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
