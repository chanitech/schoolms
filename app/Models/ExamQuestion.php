<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToSchool;

class ExamQuestion extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','exam_id', 'subject_id', 'question_no', 'description', 'max_marks'];

    protected $casts = ['max_marks' => 'decimal:2'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionScores(): HasMany
    {
        return $this->hasMany(MarkQuestionScore::class);
    }

    public function label(): string
    {
        return 'Q' . $this->question_no . ($this->description ? ': ' . $this->description : '');
    }
}
