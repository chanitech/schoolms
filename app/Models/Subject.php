<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SchoolClass;
use App\Models\User;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'teacher_id', // <-- new field for assigned teacher
    ];

    /**
     * Classes this subject is assigned to (pivot: subject_class)
     */
    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class, // Related model
            'subject_class',    // Pivot table
            'subject_id',       // Foreign key on pivot table for this model
            'class_id'          // Foreign key on pivot table for the related model
        )->withTimestamps();
    }

    /**
     * Marks for this subject
     */
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * Teacher assigned to this subject
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    
}
