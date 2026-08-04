<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = ['school_id', 'plate_number', 'name', 'capacity', 'driver_staff_id', 'status', 'notes'];

    public function driver()
    {
        return $this->belongsTo(Staff::class, 'driver_staff_id');
    }

    public function routes()
    {
        return $this->hasMany(BusRoute::class, 'bus_id');
    }
}
