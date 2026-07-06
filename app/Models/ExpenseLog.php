<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class ExpenseLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'recorded_by',
        'linked_procurement_id',
        'category',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'linked_procurement_id');
    }
}
