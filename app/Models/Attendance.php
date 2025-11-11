<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['staff_id', 'date', 'status'];

    protected $casts = [
        'date' => 'date', // Cast 'date' column to Carbon instance
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
