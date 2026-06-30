<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Budget extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
    'staff_id',         // HOD who created the budget
    'department_id',
    'month',
    'year',
    'status',           // pending, partially_approved, approved, in_use, completed
    'total_amount',
    'note',
    'current_step',     // hod, do, finance
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

 // App/Models/Budget.php
public function updateStatusBasedOnItems()
{
    // Only load items if not already loaded to avoid extra queries
    if (! $this->relationLoaded('items')) {
        $this->load('items');
    }

    // Use collection pluck to reduce loops
    $statuses = $this->items->pluck('status');

    if ($statuses->every(fn($status) => $status === 'approved')) {
        $this->status = 'approved';
        $this->current_step = 'done';
    } elseif ($statuses->every(fn($status) => $status === 'rejected')) {
        $this->status = 'declined';
        $this->current_step = null;
    } else {
        $this->status = 'partially_approved';
        $this->current_step = 'hod';
    }

    // Only save if there’s a change to avoid unnecessary writes
    if ($this->isDirty(['status', 'current_step'])) {
        $this->save();
    }
}


public function user()
{
    return $this->belongsTo(User::class, 'staff_id');
}

public function budgetItems()
{
    return $this->hasMany(BudgetItem::class);
}



}
