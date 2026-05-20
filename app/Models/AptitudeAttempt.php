<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AptitudeAttempt extends Model {
    protected $guarded = [];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function counselor() {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function answers() {
        return $this->hasMany(AptitudeAnswer::class, 'attempt_id');
    }
}
