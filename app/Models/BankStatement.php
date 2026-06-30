<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class BankStatement extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'staff_id', 'file_path', 'original_name', 'mime_type',
        'file_size', 'statement_month', 'uploaded_by',
    ];

    protected $casts = [
        'statement_month' => 'date',
        'file_size' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessor for full file URL (adjust disk as needed)
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}