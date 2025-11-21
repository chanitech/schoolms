<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AptitudeAnswer extends Model {
    protected $guarded = [];

    public function question() {
        return $this->belongsTo(AptitudeQuestion::class, 'question_id');
    }

    public function attempt() {
        return $this->belongsTo(AptitudeAttempt::class, 'attempt_id');
    }
}
