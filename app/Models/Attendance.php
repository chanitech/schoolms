<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Attendance extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id','staff_id', 'date', 'status',
        'check_in_at', 'check_out_at', 'source'];

    protected $casts = [
        'date' => 'date', // Cast 'date' column to Carbon instance
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
