<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
