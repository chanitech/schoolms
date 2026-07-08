<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class ProcurementRequest extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'requested_by',
        'approved_by',
        'headmaster_approved_by',
        'disbursed_by',
        'inventory_item_id',
        'item',
        'quantity',
        'estimated_cost',
        'actual_cost',
        'supplier',
        'status',
        'threshold_flag',
        'notes',
        'approved_at',
        'headmaster_approved_at',
        'disbursed_at',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'threshold_flag' => 'boolean',
        'approved_at' => 'datetime',
        'headmaster_approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Treasurer's approval (first stage)
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Head Master/Principal's approval (final stage, required before disbursement)
    public function headmasterApprovedBy()
    {
        return $this->belongsTo(User::class, 'headmaster_approved_by');
    }

    // Cashier who actually disbursed the payment
    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function expenseLogs()
    {
        return $this->hasMany(ExpenseLog::class, 'linked_procurement_id');
    }
}
