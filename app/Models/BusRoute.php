<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusRoute extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = ['school_id', 'bus_id', 'name', 'description', 'monthly_fee', 'status'];

    protected $casts = ['monthly_fee' => 'decimal:2'];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function stops()
    {
        return $this->hasMany(BusStop::class, 'route_id')->orderBy('stop_order');
    }

    public function assignments()
    {
        return $this->hasMany(StudentTransportAssignment::class, 'route_id');
    }

    public function activeAssignments()
    {
        return $this->assignments()->where('status', 'active');
    }
}
