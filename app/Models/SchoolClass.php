<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
