<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToSchool;

class Grade extends Model
{
    use BelongsToSchool;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'min_mark',
        'max_mark',
        'point',
        'description',
    ];

    /**
     * Check which grade corresponds to a given mark
     */
    public static function gradeForMark($mark)
    {
        return self::where('min_mark', '<=', $mark)
                   ->where('max_mark', '>=', $mark)
                   ->first();
    }
}
