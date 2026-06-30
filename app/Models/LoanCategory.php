<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class LoanCategory extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'name', 'description', 'min_amount', 'max_amount', 'max_installments',
        'interest_rate', 'eligibility_criteria', 'restrictions',
        'created_by_treasurer_id', 'is_active',
    ];

    protected $casts = [
        'eligibility_criteria' => 'array',
        'restrictions' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_treasurer_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Check if a staff member is eligible for this loan category.
     */
    public function isStaffEligible(Staff $staff): bool
    {
        $criteria = $this->eligibility_criteria ?? [];

        // Example criteria: min_salary, min_years_employed
        if (isset($criteria['min_salary']) && $staff->basic_salary < $criteria['min_salary']) {
            return false;
        }
        if (isset($criteria['min_years_employed']) && $staff->years_employed < $criteria['min_years_employed']) {
            return false;
        }

        // Restrictions: check if multiple active loans are forbidden
        $restrictions = $this->restrictions ?? [];
        if (isset($restrictions['allow_multiple_active_loans']) && $restrictions['allow_multiple_active_loans'] === false) {
            if ($staff->hasActiveLoan()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate applied amount against category limits.
     */
    public function isAmountValid(float $amount): bool
    {
        return $amount >= $this->min_amount && $amount <= $this->max_amount;
    }
}