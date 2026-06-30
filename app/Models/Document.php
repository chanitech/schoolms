<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToSchool;

class Document extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title', 'description', 'category',
        'academic_session_id', 'class_id', 'subject',
        'language', 'document_date', 'author', 'tags',
        'file_path', 'original_name', 'file_size', 'mime_type',
        'download_count', 'is_featured', 'is_restricted',
        'uploaded_by',
    ];

    protected $casts = [
        'tags'          => 'array',
        'document_date' => 'date',
        'is_featured'   => 'boolean',
        'is_restricted' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIconClassAttribute(): string
    {
        return match(true) {
            $this->is_pdf                                      => 'fas fa-file-pdf text-danger',
            $this->is_image                                    => 'fas fa-file-image text-info',
            str_contains($this->mime_type, 'word')             => 'fas fa-file-word text-primary',
            str_contains($this->mime_type, 'excel') ||
            str_contains($this->mime_type, 'spreadsheet')      => 'fas fa-file-excel text-success',
            str_contains($this->mime_type, 'powerpoint') ||
            str_contains($this->mime_type, 'presentation')     => 'fas fa-file-powerpoint text-warning',
            str_contains($this->mime_type, 'zip') ||
            str_contains($this->mime_type, 'rar')              => 'fas fa-file-archive text-warning',
            default                                            => 'fas fa-file-alt text-secondary',
        };
    }

    public function getTagsListAttribute(): array
    {
        return $this->tags ?? [];
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_restricted', false);
    }

    // ── Static Helpers ─────────────────────────────────────────────────────

    public static function categories(): array
    {
        return [
            'past_papers' => 'Past Papers',
            'letters'     => 'Official Letters',
            'circulars'   => 'Circulars & Notices',
            'reports'     => 'Reports',
            'forms'       => 'Forms & Templates',
            'curriculum'  => 'Curriculum & Syllabi',
            'policies'    => 'Policies & Guidelines',
            'general'     => 'General',
        ];
    }

    public static function languages(): array
    {
        return ['English', 'Swahili', 'Arabic', 'French', 'Both (EN/SW)'];
    }
}
