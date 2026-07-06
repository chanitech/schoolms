<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Payment extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'student_bill_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'recorded_by',
        'received_by',
        'note',
        'class_id',
        'verified_by',
        'status',
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

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
