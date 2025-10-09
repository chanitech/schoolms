<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',       // The staff member who requested the leave
        'requested_to',   // The staff member (HOD/Director) who receives the request
        'start_date',
        'end_date',
        'type',
        'status',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * The staff member who made the leave request.
     */
    public function requester()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * The staff member who received the request (HOD or Director).
     */
    public function recipient()
    {
        return $this->belongsTo(Staff::class, 'requested_to');
    }

    /**
     * Scope for pending leaves.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved leaves.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected leaves.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
