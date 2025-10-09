<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
   protected $fillable = [
    'name',
    'email',
    'password',
    'first_name',
    'last_name',
    'phone',
    'department_id',
    'position',
    'photo',
    'role',
];

// Optional: helper for full name
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Department relationship
public function department()
{
    return $this->belongsTo(\App\Models\Department::class);
}


public function staffProfile()
{
    return $this->hasOne(\App\Models\Staff::class);
}



public function staff()
{
    return $this->hasOne(\App\Models\Staff::class, 'user_id');
}







    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
}
