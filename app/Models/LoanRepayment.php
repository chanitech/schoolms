<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'installment_number', 'amount', 'due_date',
        'paid_date', 'status', 'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    // Mark as paid
    public function markAsPaid(?string $reference = null): void
    {
        $this->paid_date = now();
        $this->status = 'paid';
        $this->payment_reference = $reference;
        $this->save();

        // Check if all installments of the loan are paid
        $loan = $this->loan;
        $remaining = $loan->repayments()->where('status', '!=', 'paid')->count();
        if ($remaining === 0) {
            $loan->status = 'closed';
            $loan->save();
        }
    }

    // Check if this installment is overdue
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }
}