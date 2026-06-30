<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToSchool;

class DailyReport extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'teacher_id', 'report_date', 'status',
        'summary', 'challenges', 'next_day_plan', 'additional_notes',
        'submitted_at',
    ];

    protected $casts = [
        'report_date'  => 'date',
        'submitted_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DailyReportActivity::class);
    }

    public function sessionLogs(): HasMany
    {
        return $this->hasMany(TimetableSessionLog::class, 'teacher_id', 'teacher_id')
            ->whereColumn('session_date', 'daily_reports.report_date');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
