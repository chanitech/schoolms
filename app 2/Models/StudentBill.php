<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'bill_id',
        'total_amount', // total bill amount
        'amount_paid',  // amount already paid
        'balance',      // remaining balance
        'status',       // 'unpaid', 'partial', 'paid', 'overpaid'
        'due_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance'      => 'decimal:2',
        'due_date'     => 'date',
    ];

    // Relationships
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Update balance and status whenever amount_paid changes
     */
    public function updateBalanceAndStatus()
    {
        $this->balance = $this->total_amount - $this->amount_paid;

        if ($this->amount_paid == 0) {
            $this->status = 'unpaid';
        } elseif ($this->amount_paid < $this->total_amount) {
            $this->status = 'partial';
        } elseif ($this->amount_paid == $this->total_amount) {
            $this->status = 'paid';
        } else { // overpaid
            $this->status = 'overpaid';
        }

        $this->save();
    }
}
