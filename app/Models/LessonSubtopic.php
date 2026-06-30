<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToSchool;

class LessonSubtopic extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'lesson_topic_id', 'title', 'order_no',
        'status', 'date_covered', 'notes', 'covered_by',
        'lesson_plan_content',
    ];

    protected $casts = [
        'date_covered' => 'date',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }

    public function coveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'covered_by');
    }

    public function isCovered(): bool
    {
        return $this->status === 'covered';
    }
}
