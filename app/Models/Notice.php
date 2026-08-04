<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'title', 'body', 'audience', 'pinned', 'expires_at', 'posted_by'];

    protected $casts = ['pinned' => 'boolean', 'expires_at' => 'date'];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /** Active (not expired) notices visible to a given audience, pinned first. */
    public static function visibleTo(array $audiences, int $limit = 5)
    {
        return static::whereIn('audience', $audiences)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()))
            ->orderByDesc('pinned')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
