<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToSchool;

class InventoryItem extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'category_id', 'name', 'code', 'description', 'unit',
        'quantity_in_stock', 'minimum_stock', 'unit_cost',
        'condition', 'location', 'notes', 'managed_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    public function isLowStock(): bool
    {
        return $this->minimum_stock > 0 && $this->quantity_in_stock <= $this->minimum_stock;
    }
}
