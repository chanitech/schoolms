<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',             // <-- add this
        'first_name',
        'last_name',
        'gender',
        'relation_to_student',
        'phone',
        'email',
        'address',
        'occupation',
        'national_id',
    ];

    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // app/Models/Guardian.php
public function user()
{
    return $this->belongsTo(User::class);
}
}
