<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'created_by',
        'date',
        // q1..q17
        'q1','q2','q3','q4','q5','q6','q7','q8','q9','q10','q11','q12','q13','q14','q15','q16','q17',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // relationships
    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
