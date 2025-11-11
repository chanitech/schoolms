<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'due_date',
        'class_id',
    ];

    // Automatically cast attributes
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',          // Carbon instance
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function studentBills()
    {
        return $this->hasMany(StudentBill::class);
    }
}
