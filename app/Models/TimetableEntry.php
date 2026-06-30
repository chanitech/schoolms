<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToSchool;

class TimetableEntry extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'timetable_id', 'class_id', 'subject_id', 'teacher_id',
        'invigilator_ids',
        'day_of_week', 'period_id',
        'exam_date', 'start_time', 'end_time',
        'room', 'notes',
    ];

    protected $casts = [
        'exam_date'      => 'date',
        'invigilator_ids' => 'array',
    ];

    public static array $DAYS = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    public function sessionLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TimetableSessionLog::class, 'timetable_entry_id');
    }

    public function logForDate(string $date): ?TimetableSessionLog
    {
        return $this->sessionLogs()->whereDate('session_date', $date)->first();
    }

    public function getDayNameAttribute(): string
    {
        return self::$DAYS[$this->day_of_week] ?? '';
    }
}
