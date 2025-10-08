<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'position',
        'photo',
        'role',
        'user_id', // link to users table
    ];

    // Full name helper
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Department relationship
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Linked User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
