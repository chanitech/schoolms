<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Concerns\BelongsToSchool;

class LessonTopic extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','lesson_plan_id', 'title', 'order_no'];

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class);
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(LessonSubtopic::class)->orderBy('order_no');
    }

    public function sessionLogs(): HasMany
    {
        return $this->hasMany(TimetableSessionLog::class, 'lesson_topic_id');
    }

    public function completionStats(): array
    {
        $total   = $this->subtopics()->count();
        $covered = $this->subtopics()->where('status', 'covered')->count();
        return [
            'total'   => $total,
            'covered' => $covered,
            'pct'     => $total > 0 ? round(($covered / $total) * 100, 1) : 0,
        ];
    }
}
