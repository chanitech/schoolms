<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'student_bill_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'recorded_by',
        'received_by',
        'note',
    ];

    // ✅ Cast payment_date to Carbon
    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function studentBill()
    {
        return $this->belongsTo(StudentBill::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
