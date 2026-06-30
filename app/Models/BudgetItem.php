<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class BudgetItem extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'budget_id',
        'item',
        'description',
        'price',
        'status',
        'approved_by',
        'note',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function invoice()
{
    return $this->hasOne(Invoice::class);
}

}
