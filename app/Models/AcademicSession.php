<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Support\Facades\Cache;

class AcademicSession extends Model
{
    use BelongsToSchool;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    // Cast dates and boolean
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'academic_session_id');
    }

    protected static function booted(): void
    {
        // The cache this invalidates is per-school (see current(), below) —
        // every school has its own "current session", so a save/delete for
        // one school must never clear another's cached value. Hooked at the
        // model level rather than in each controller that can flip
        // is_current, so a call site added later can't forget to invalidate it.
        static::saved(fn (self $session) => Cache::forget("academic-session.current.{$session->school_id}"));
        static::deleted(fn (self $session) => Cache::forget("academic-session.current.{$session->school_id}"));
    }

    /**
     * The school's current academic session, cached.
     *
     * Read on nearly every dashboard load, but changes at most a couple of
     * times a year — cached with a long TTL as a safety net, invalidated
     * immediately on write via the booted() hook above so the TTL is never
     * actually what makes this correct.
     *
     * Cache key is scoped to the bound tenant: without that, one school's
     * current session would leak into another's — the exact bug class the
     * BelongsToSchool scope on this model exists to prevent everywhere else.
     */
    public static function current(): ?self
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        return Cache::remember(
            "academic-session.current.{$schoolId}",
            now()->addHours(6),
            fn () => static::where('is_current', true)->first()
        );
    }
}
