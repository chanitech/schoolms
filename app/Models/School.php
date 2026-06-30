<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo', 'email', 'phone',
        'address', 'motto', 'website',
        'subscription_status', 'subscription_expires_at', 'plan',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->subscription_status === 'active'
            || ($this->subscription_status === 'trial'
                && ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture()));
    }

    // All relationships bypass the BelongsToSchool global scope so the super-admin
    // can query any school regardless of which tenant is currently active.
    public function users()         { return $this->hasMany(User::class)->withoutGlobalScope('school'); }
    public function staff()         { return $this->hasMany(Staff::class)->withoutGlobalScope('school'); }
    public function students()      { return $this->hasMany(Student::class)->withoutGlobalScope('school'); }
    public function guardians()     { return $this->hasMany(Guardian::class)->withoutGlobalScope('school'); }
    public function exams()         { return $this->hasMany(Exam::class)->withoutGlobalScope('school'); }
    public function schoolClasses() { return $this->hasMany(SchoolClass::class)->withoutGlobalScope('school'); }
    public function subjects()      { return $this->hasMany(Subject::class)->withoutGlobalScope('school'); }
    public function departments()   { return $this->hasMany(Department::class)->withoutGlobalScope('school'); }
    public function schoolInfo()    { return $this->hasOne(SchoolInfo::class)->withoutGlobalScope('school'); }
}
