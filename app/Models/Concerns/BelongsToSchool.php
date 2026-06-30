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
                $table = $builder->getModel()->getTable();
                $builder->where("{$table}.school_id", app('currentSchool')->id);
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
