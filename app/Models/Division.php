<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'min_points',
        'max_points',
        'description',
    ];

    /**
     * Get division based on total points
     */
    public static function getDivisionByPoints($points)
    {
        return self::where('min_points', '<=', $points)
                   ->where('max_points', '>=', $points)
                   ->first();
    }
}
