<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Department extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'head_id',
        'rank_requires_7_subjects', // new field
    ];

    /**
     * The head of the department (a staff member).
     */
    public function head()
    {
        return $this->belongsTo(Staff::class, 'head_id');
    }

    /**
     * All staff members belonging to this department.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Subjects taught under this department.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Students who belong to this department.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
