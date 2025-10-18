<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Mark;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'teacher_id',
    ];

    /**
     * 🔹 Classes this subject belongs to (many-to-many)
     */
    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'subject_class',
            'subject_id',
            'class_id'
        )->withTimestamps();
    }

    /**
     * 🔹 Marks related to this subject
     */
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * 🔹 Teacher assigned to this subject
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * 🔹 Accessor: Get the teacher’s full name easily
     */
    public function getTeacherNameAttribute(): string
    {
        return $this->teacher
            ? "{$this->teacher->first_name} {$this->teacher->last_name}"
            : '—';
    }
}
