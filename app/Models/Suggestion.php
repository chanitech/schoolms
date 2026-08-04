<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'submitted_by', 'submitter_role', 'is_anonymous',
        'category', 'subject', 'message', 'status',
        'admin_response', 'responded_by', 'responded_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'responded_at' => 'datetime',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
