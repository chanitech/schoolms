<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToSchool;

class TimetablePeriod extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','name', 'start_time', 'end_time', 'is_break', 'is_active', 'order_no'];

    protected $casts = ['is_break' => 'boolean', 'is_active' => 'boolean'];

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'period_id');
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) round(
            (strtotime($this->end_time) - strtotime($this->start_time)) / 60
        );
    }

    public static function active(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('order_no')->get();
    }
}
