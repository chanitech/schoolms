<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AptitudeQuestion extends Model {
    protected $guarded = [];
    protected $casts = ['options' => 'array'];

    public function answers() {
        return $this->hasMany(AptitudeAnswer::class, 'question_id');
    }
}
