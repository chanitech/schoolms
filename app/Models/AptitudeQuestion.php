<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class AptitudeQuestion extends Model {
    use BelongsToSchool;

    protected $guarded = [];
    protected $casts = ['options' => 'array'];

    public function answers() {
        return $this->hasMany(AptitudeAnswer::class, 'question_id');
    }
}
