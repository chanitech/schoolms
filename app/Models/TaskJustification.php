<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskJustification extends Model
{
    protected $fillable = [
        'task_log_id',
        'submitted_by',
        'reason',
        'submitted_at',
        'treasurer_reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'treasurer_reviewed_at' => 'datetime',
    ];

    public function taskLog()
    {
        return $this->belongsTo(TaskLog::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
