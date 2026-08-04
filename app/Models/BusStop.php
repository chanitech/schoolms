<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class BusStop extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'route_id', 'name', 'stop_order', 'pickup_time', 'dropoff_time'];

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }
}
