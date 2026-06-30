<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToSchool;

class InventoryTransaction extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'item_id', 'type', 'quantity', 'balance_after',
        'reference_no', 'issued_to', 'remarks', 'user_id', 'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDeduction(): bool
    {
        return in_array($this->type, ['issue', 'damage', 'disposal']);
    }
}
