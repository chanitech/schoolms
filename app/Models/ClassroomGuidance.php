<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomGuidance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'date',
        'tasks',
        'achievements',
        'challenges',
        'created_by',
    ];

    // Ensure 'date' is treated as a Carbon instance
    protected $casts = [
        'date' => 'date',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
