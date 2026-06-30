<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToSchool;

class MarkQuestionScore extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','mark_id', 'exam_question_id', 'score'];

    protected $casts = ['score' => 'decimal:2'];

    public function mark(): BelongsTo
    {
        return $this->belongsTo(Mark::class);
    }

    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }
}
