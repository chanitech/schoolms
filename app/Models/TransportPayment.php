<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class TransportPayment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'transport_fee_id', 'amount', 'payment_method',
        'reference', 'payment_date', 'recorded_by', 'note',
    ];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];

    public function fee()
    {
        return $this->belongsTo(TransportFee::class, 'transport_fee_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
