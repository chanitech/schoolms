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
        'name',               // e.g., Midterm, Final
        'term',               // e.g., Term 1, Term 2
        'academic_session_id',
    ];

    /**
     * Academic session this exam belongs to
     */
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }
}
