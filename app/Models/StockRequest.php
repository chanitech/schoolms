<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class StockRequest extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'requested_by',
        'inventory_item_id',
        'item',
        'quantity',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'procurement_request_id',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }
}
