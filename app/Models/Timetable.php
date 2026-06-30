<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToSchool;

class Timetable extends Model
{
    use BelongsToSchool;

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'type', 'academic_session_id', 'status',
        'class_ids', 'settings', 'notes',
        'created_by', 'published_by', 'published_at',
    ];

    protected $casts = [
        'class_ids'    => 'array',
        'settings'     => 'array',
        'published_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TimetableReview::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'draft'          => '<span class="badge badge-secondary">Draft</span>',
            'pending_review' => '<span class="badge badge-warning">Pending Review</span>',
            'published'      => '<span class="badge badge-success">Published</span>',
            'rejected'       => '<span class="badge badge-danger">Rejected</span>',
            default          => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }

    // Get HOD approvals count
    public function hodApprovalsCount(): int
    {
        return $this->reviews()->where('reviewer_role', 'hod')->where('action', 'approved')->count();
    }
}
