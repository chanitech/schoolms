<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'loan_category_id', 'amount_applied', 'amount_approved',
        'interest_rate_applied', 'installments', 'salary_at_application',
        'application_date', 'approval_date', 'disbursement_date', 'expected_end_date',
        'approval_level', 'chief_accountant_approved_by', 'chief_accountant_approved_at',
        'accountant_approved_by', 'accountant_approved_at',
        'treasurer_approved_by', 'treasurer_approved_at',
        'rejection_reason', 'status', 'treasurer_notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'expected_end_date' => 'date',
        'chief_accountant_approved_at' => 'datetime',
        'accountant_approved_at' => 'datetime',
        'treasurer_approved_at' => 'datetime',
        'amount_applied' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'interest_rate_applied' => 'decimal:2',
        'salary_at_application' => 'decimal:2',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function category()
    {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    // Approval chain helpers
    public function chiefAccountantApprover()
    {
        return $this->belongsTo(User::class, 'chief_accountant_approved_by');
    }

    public function accountantApprover()
    {
        return $this->belongsTo(User::class, 'accountant_approved_by');
    }

    public function treasurerApprover()
    {
        return $this->belongsTo(User::class, 'treasurer_approved_by');
    }

    // Status accessors
    public function isFullyApproved(): bool
    {
        return $this->approval_level === 3 && $this->status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // Get the next required role for approval (0=none, 1=Chief Accountant, 2=Accountant, 3=Treasurer)
    public function getNextApproverRole(): ?string
    {
        return match ($this->approval_level) {
            0 => 'chief-accountant',
            1 => 'accountant',
            2 => 'treasurer',
            default => null,
        };
    }

    // Approve by a user (checks role & level)
    public function approveBy(User $user): bool
    {
        if ($this->status !== 'pending') return false;

        if ($user->hasRole('chief-accountant') && $this->approval_level === 0) {
            $this->approval_level = 1;
            $this->chief_accountant_approved_by = $user->id;
            $this->chief_accountant_approved_at = now();
        } elseif ($user->hasRole('accountant') && $this->approval_level === 1) {
            $this->approval_level = 2;
            $this->accountant_approved_by = $user->id;
            $this->accountant_approved_at = now();
        } elseif ($user->hasRole('treasurer') && $this->approval_level === 2) {
            $this->approval_level = 3;
            $this->treasurer_approved_by = $user->id;
            $this->treasurer_approved_at = now();
            $this->approval_date = now();
            $this->status = 'approved';
        } else {
            return false;
        }

        $this->save();

        // If fully approved, we might automatically disburse? Usually manual disbursement.
        // But we can also set a flag. We'll leave disbursement_date to be set by treasurer later.
        return true;
    }

    // Reject the loan
    public function rejectBy(User $user, string $reason): bool
    {
        if ($this->status !== 'pending') return false;
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
        return true;
    }

    // Disburse loan: sets disbursement_date, status to 'active', generates repayments
    public function disburse(?Carbon $disbursementDate = null): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Loan must be approved before disbursement.');
        }
        $this->disbursement_date = $disbursementDate ?? now();
        $this->status = 'active';
        $this->expected_end_date = $this->disbursement_date->copy()->addMonths($this->installments);
        $this->save();

        // Generate repayment schedule
        $this->generateRepaymentSchedule();
    }

    // Generate installment repayments (call after disbursement)
    public function generateRepaymentSchedule(): void
    {
        if (!$this->disbursement_date) {
            throw new \Exception('Disbursement date not set.');
        }
        // Delete old schedule if any
        $this->repayments()->delete();

        $amount = $this->amount_approved ?? $this->amount_applied;
        // Simple interest calculation: total interest = principal * rate * years
        $years = $this->installments / 12;
        $totalInterest = $amount * ($this->interest_rate_applied / 100) * $years;
        $totalRepayable = $amount + $totalInterest;
        $installmentAmount = round($totalRepayable / $this->installments, 2);

        for ($i = 1; $i <= $this->installments; $i++) {
            $dueDate = $this->disbursement_date->copy()->addMonths($i);
            $this->repayments()->create([
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
        }
    }

    // Remaining balance (total repayable - sum of paid amounts)
    public function getRemainingBalanceAttribute(): float
    {
        $totalRepayable = $this->repayments()->sum('amount');
        $totalPaid = $this->repayments()->where('status', 'paid')->sum('amount');
        return round($totalRepayable - $totalPaid, 2);
    }

    // Check if any repayment is overdue
    public function hasOverdue(): bool
    {
        return $this->repayments()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->exists();
    }

    // Update overdue statuses (can be run via cron)
    public static function markOverdueLoans(): void
    {
        $loans = self::where('status', 'active')->get();
        foreach ($loans as $loan) {
            $loan->repayments()
                ->where('due_date', '<', now())
                ->where('status', 'pending')
                ->update(['status' => 'overdue']);
        }
    }

    
}