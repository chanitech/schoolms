<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * Raw audit trail of every fingerprint scan received from a school's relay,
 * whether or not it could be matched to a Staff record. Attendance
 * check-in/out times are derived FROM this table (first-in/last-out per
 * staff per day), not merged incrementally into `attendances`.
 */
class BiometricScanLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'staff_id',
        'device_user_id',
        'device_serial',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
