<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class AptitudeQuestion extends Model {
    use BelongsToSchool;

    protected $guarded = [];

    // Always an array on read. Older rows were saved json_encode()d on top of
    // the old 'array' cast, so some are double-encoded strings; decode twice
    // when needed. Writes accept an array or an already-encoded JSON string.
    protected function options(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $decoded = is_string($value) ? json_decode($value, true) : $value;
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                return is_array($decoded) ? $decoded : [];
            },
            set: fn ($value) => $value === null || is_string($value) ? $value : json_encode($value),
        );
    }

    public function answers() {
        return $this->hasMany(AptitudeAnswer::class, 'question_id');
    }
}
