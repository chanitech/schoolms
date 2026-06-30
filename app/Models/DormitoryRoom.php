<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToSchool;

class DormitoryRoom extends Model
{
    use BelongsToSchool;

    use SoftDeletes;

    protected $table = 'dormitory_rooms';

    protected $fillable = [
        'school_id',
        'dormitory_id', 'room_number', 'floor', 'capacity', 'occupied_beds',
        'room_type', 'has_attached_bathroom', 'has_balcony', 'is_available',
        'facilities', 'description'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'occupied_beds' => 'integer',
        'has_attached_bathroom' => 'boolean',
        'has_balcony' => 'boolean',
        'is_available' => 'boolean',
    ];

    // Parent dormitory
    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    // Beds in this room
    public function beds()
    {
        return $this->hasMany(DormitoryBed::class, 'room_id');
    }

    // Available beds
    public function availableBeds()
    {
        return $this->beds()->where('status', 'available');
    }

    // Available beds count
    public function getAvailableBedsCountAttribute()
    {
        return $this->capacity - $this->occupied_beds;
    }

    // Students currently in this room
    public function currentStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            DormitoryBed::class,
            'room_id',
            'id',
            'id',
            'current_student_id'
        )->whereNotNull('dormitory_beds.current_student_id');
    }

    // Scope for available rooms
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->whereRaw('capacity > occupied_beds');
    }

    // Scope by floor
    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }
}