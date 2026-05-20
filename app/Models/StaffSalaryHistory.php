<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSalaryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'old_salary', 'new_salary', 'effective_date', 'changed_by', 'reason',
    ];

    protected $casts = [
        'old_salary' => 'decimal:2',
        'new_salary' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}