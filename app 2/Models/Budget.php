<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'department_id',
        'month',
        'year',
        'status',
        'total_amount',
        'note',
    ];

    // Updated relationship to use User model instead of Staff
    public function staff() {
        return $this->belongsTo(\App\Models\User::class, 'staff_id');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function items() {
        return $this->hasMany(BudgetItem::class);
    }

    public function calculateTotal() {
        $this->total_amount = $this->items()->sum('price');
        $this->save();
    }
    public function submitted_by() {
    return $this->belongsTo(User::class, 'submitted_by_id');
}
}
