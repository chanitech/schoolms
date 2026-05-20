<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCounselingSessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'members',                    // JSON array of student IDs or names
        'date',
        'time',
        'session_number',
        'presenting_problem',
        'work_done',
        'assessment_progress',
        'intervention_plan',
        'follow_up',
        'biopsychosocial_formulation', // JSON of 4P's
        'user_id',                     // counselor who created report
    ];

    // Cast JSON fields to array automatically
    protected $casts = [
        'members' => 'array',
        'biopsychosocial_formulation' => 'array',
    ];

    /**
     * Relationship to the counselor who created this report
     */
    public function counselor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Many-to-many relationship with students via pivot table
     * Pivot table: group_counseling_student
     * Pivot keys: report_id, student_id
     */
public function students()
{
    return $this->belongsToMany(
        Student::class,
        'group_counseling_student',               // pivot table
        'group_counseling_session_report_id',    // foreign key on pivot for this model
        'student_id'                              // foreign key on pivot for related model
    )->withTimestamps();
}


public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}





    
}
