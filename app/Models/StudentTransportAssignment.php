<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StudentTransportAssignment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_id', 'route_id', 'stop_id', 'status',
        'start_date', 'end_date', 'notes', 'assigned_by',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }

    public function stop()
    {
        return $this->belongsTo(BusStop::class, 'stop_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
