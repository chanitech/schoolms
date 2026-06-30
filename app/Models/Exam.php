<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToSchool;

class Exam extends Model
{
    use BelongsToSchool;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'term',
        'academic_session_id',
        'include_in_term_final',
        'include_in_year_final',
        'is_terminal_exam',
        'is_annual_exam',
        'status',
        'reviewed_by',
        'reviewed_at',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'include_in_term_final' => 'boolean',
        'include_in_year_final' => 'boolean',
        'is_terminal_exam'      => 'boolean',
        'is_annual_exam'        => 'boolean',
        'reviewed_at'           => 'datetime',
        'published_at'          => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // ── Status helpers ─────────────────────────────────────────────────────
    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isReviewed(): bool   { return $this->status === 'reviewed'; }
    public function isPublished(): bool  { return $this->status === 'published'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'reviewed'  => 'Reviewed',
            'published' => 'Published',
            default     => 'Draft',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'reviewed'  => 'warning',
            'published' => 'success',
            default     => 'secondary',
        };
    }
}
