<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AcademicSession;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',                 // e.g., Midterm, Final
        'term',                 // e.g., Term 1, Term 2
        'academic_session_id',
        'include_in_term_final', // checkbox to include in term average
        'include_in_year_final', // checkbox to include in year/annual average
        'is_terminal_exam',      // flag to mark terminal exam
    ];

    // Cast checkboxes and flags to boolean automatically
    protected $casts = [
        'include_in_term_final' => 'boolean',
        'include_in_year_final' => 'boolean',
        'is_terminal_exam'      => 'boolean',
    ];

    /**
     * Academic session this exam belongs to
     */
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /**
     * Marks associated with this exam
     */
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }


}
