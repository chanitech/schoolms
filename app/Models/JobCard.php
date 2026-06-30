<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class JobCard extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'assigned_by',
        'assigned_to',
        'status',
        'rating',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    // Assigned TO a staff member
    public function assignee()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    // Assigned BY a staff member
    public function assigner()
    {
        return $this->belongsTo(Staff::class, 'assigned_by');
    }
}
