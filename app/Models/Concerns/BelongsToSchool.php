<?php

namespace App\Models\Concerns;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        // Auto-fill school_id on every new record
        static::creating(function ($model) {
            if (empty($model->school_id) && app()->bound('currentSchool')) {
                $model->school_id = app('currentSchool')->id;
            }
        });

        // Global scope: every query is automatically scoped to the current school
        static::addGlobalScope('school', function (Builder $builder) {
            if (app()->bound('currentSchool')) {
                $table    = $builder->getModel()->getTable();
                $schoolId = app('currentSchool')->id;

                // Super admins aren't tied to a single school (school_id is
                // often null) but this scope also runs when Laravel's auth
                // guard re-fetches the logged-in user on every request — if
                // a super admin isn't exempted here, they'd get silently
                // logged out the moment tenant_school_id is set in session,
                // since the scoped lookup would never match their row.
                if ($table === 'users') {
                    $builder->where(function ($q) use ($table, $schoolId) {
                        $q->where("{$table}.school_id", $schoolId)
                          ->orWhere("{$table}.is_super_admin", true);
                    });
                } else {
                    $builder->where("{$table}.school_id", $schoolId);
                }
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Escape hatch for super-admin cross-school queries
    public static function withoutSchoolScope(): Builder
    {
        return static::withoutGlobalScope('school');
    }
}
