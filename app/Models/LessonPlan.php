<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Concerns\BelongsToSchool;

class LessonPlan extends Model
{
    use BelongsToSchool;

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_session_id', 'subject_id', 'class_id',
        'teacher_id', 'title', 'description',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LessonTopic::class)->orderBy('order_no');
    }

    public function subtopics(): HasManyThrough
    {
        return $this->hasManyThrough(LessonSubtopic::class, LessonTopic::class);
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
