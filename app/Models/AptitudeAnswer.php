<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class AptitudeAnswer extends Model {
    use BelongsToSchool;

    protected $guarded = [];

    public function question() {
        return $this->belongsTo(AptitudeQuestion::class, 'question_id');
    }

    public function attempt() {
        return $this->belongsTo(AptitudeAttempt::class, 'attempt_id');
    }
}
