<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class TaskLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'role',
        'task_description',
        'deadline',
        'percent_complete',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'is_flagged_compliance',
        'is_flagged_exceeds',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_flagged_compliance' => 'boolean',
        'is_flagged_exceeds' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function justifications()
    {
        return $this->hasMany(TaskJustification::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'approved' && $this->deadline < now();
    }

    public function needsJustification(): bool
    {
        return $this->status === 'overdue' && !$this->justifications()->exists();
    }
}
