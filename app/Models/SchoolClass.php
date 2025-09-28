<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\Staff;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'level',
        'section',
        'capacity',
        'class_teacher_id',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }

    public function subjects()
{
    return $this->belongsToMany(
        Subject::class,
        'subject_class',
        'class_id',
        'subject_id'
    )->withTimestamps();
}

}
