<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DormitoryBedAllocation extends Model
{
    use SoftDeletes;

    protected $table = 'dormitory_bed_allocations';

    protected $fillable = [
        'bed_id', 'student_id', 'academic_session_id', 'allocation_date',
        'start_date', 'end_date', 'status', 'notes', 'allocated_by'
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Bed being allocated
    public function bed()
    {
        return $this->belongsTo(DormitoryBed::class, 'bed_id');
    }

    // Student allocated
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Academic session
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    // User who made the allocation
    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    // Scope for active allocations
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for completed allocations
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}