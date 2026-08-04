<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class TransportFee extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_id', 'route_id', 'month', 'year',
        'amount', 'amount_paid', 'balance', 'status', 'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'amount_paid' => 'decimal:2', 'balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }

    public function payments()
    {
        return $this->hasMany(TransportPayment::class);
    }

    public function periodLabel(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}
