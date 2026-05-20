<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DormitoryBed extends Model
{
    use SoftDeletes;

    protected $table = 'dormitory_beds';

    protected $fillable = [
        'room_id', 'bed_number', 'bed_type', 'status', 'current_student_id', 'features'
    ];

    // Parent room
    public function room()
    {
        return $this->belongsTo(DormitoryRoom::class, 'room_id');
    }

    // Current student occupying this bed
    public function currentStudent()
    {
        return $this->belongsTo(Student::class, 'current_student_id');
    }

    // Allocation history
    public function allocations()
    {
        return $this->hasMany(DormitoryBedAllocation::class, 'bed_id');
    }

    // Active allocation
    public function activeAllocation()
    {
        return $this->hasOne(DormitoryBedAllocation::class, 'bed_id')
                    ->where('status', 'active');
    }

    // Check if bed is occupied
    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    // Check if bed is available
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    // Scope for available beds
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Scope for occupied beds
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }
}