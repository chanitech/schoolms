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

    // Slugs that used to identify a school before it was shortened (e.g. for
    // the School Code login flow). External integrations — kass.ac.tz's
    // public-facing site calling our /api/public/* endpoints, in
    // particular — hardcode a slug on their end and have no way to know it
    // changed, so old slugs must keep resolving indefinitely rather than
    // silently 404ing the moment we rename one internally.
    private const LEGACY_SLUG_ALIASES = [
        'kitungwa-adventist-secondary-school' => 'kitungwa',
    ];

    public static function resolveBySlug(string $slug): self
    {
        $slug = self::LEGACY_SLUG_ALIASES[$slug] ?? $slug;

        return static::where('slug', $slug)->firstOrFail();
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
