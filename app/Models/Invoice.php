<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Invoice extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
    'budget_id',
    'budget_item_id',
    'amount',
    'status',
    'approved_by_do_id',
    'paid_by_finance_id',
    'payment_date',
    'note',
];


    protected $casts = [
        'payment_date' => 'datetime', // <-- this ensures you can use ->format()
    ];


    public function budgetItem()
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    

    



    public function approvedBy()
{
    return $this->belongsTo(User::class, 'approved_by_do_id');
}

public function paidBy()
{
    return $this->belongsTo(User::class, 'paid_by_finance_id');
}

}

