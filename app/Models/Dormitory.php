<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToSchool;

class Dormitory extends Model
{
    use BelongsToSchool;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'capacity',
        'gender',
        'dorm_master_id',
    ];

    // Students in this dormitory (current allocation)
    public function students()
    {
        return $this->hasMany(Student::class, 'dormitory_id');
    }

    // Dorm master (staff)
    public function dormMaster()
    {
        return $this->belongsTo(Staff::class, 'dorm_master_id');
    }

    // Rooms in this dormitory
    public function rooms()
    {
        return $this->hasMany(DormitoryRoom::class, 'dormitory_id');
    }

    // Available rooms
    public function availableRooms()
    {
        return $this->rooms()->where('is_available', true);
    }

    // All beds across all rooms
    public function beds()
    {
        return $this->hasManyThrough(DormitoryBed::class, DormitoryRoom::class, 'dormitory_id', 'room_id');
    }

    // Available beds
    public function availableBeds()
    {
        return $this->beds()->where('status', 'available');
    }

    // Occupied beds count
    public function getOccupiedBedsCountAttribute()
    {
        return $this->beds()->where('status', 'occupied')->count();
    }

    // Available beds count
    public function getAvailableBedsCountAttribute()
    {
        return $this->beds()->where('status', 'available')->count();
    }

    // Occupancy rate
    public function getOccupancyRateAttribute()
    {
        if ($this->capacity == 0) return 0;
        return round(($this->occupied_beds_count / $this->capacity) * 100, 2);
    }

    // Scope for active dormitories
    public function scopeActive($query)
    {
        return $query->whereHas('rooms');
    }

    // Scope by gender
    public function scopeForGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
}