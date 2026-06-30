<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Concerns\BelongsToSchool;

class TimetableSessionLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'timetable_entry_id', 'teacher_id', 'class_id', 'subject_id', 'period_id',
        'session_date', 'status', 'notes', 'recorded_by', 'lesson_topic_id',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class, 'timetable_entry_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }

    public function coveredSubtopics(): BelongsToMany
    {
        return $this->belongsToMany(LessonSubtopic::class, 'session_log_subtopics', 'session_log_id', 'subtopic_id');
    }

    public static array $STATUSES = [
        'attended' => ['label' => 'Attended', 'color' => 'success',  'icon' => 'fas fa-check-circle'],
        'late'     => ['label' => 'Late',     'color' => 'warning',  'icon' => 'fas fa-clock'],
        'absent'   => ['label' => 'Absent',   'color' => 'danger',   'icon' => 'fas fa-times-circle'],
        'other'    => ['label' => 'Other',    'color' => 'secondary','icon' => 'fas fa-question-circle'],
    ];

    public function isPresent(): bool
    {
        return in_array($this->status, ['attended', 'late']);
    }
}
