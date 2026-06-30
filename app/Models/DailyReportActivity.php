<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToSchool;

class DailyReportActivity extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'daily_report_id', 'type', 'title', 'description', 'time_from', 'time_to',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
}
