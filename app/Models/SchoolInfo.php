<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class SchoolInfo extends Model
{
    use BelongsToSchool;

    protected $table = 'school_infos';

    protected $fillable = [
        'school_id',
        'name', 'motto', 'logo', 'email', 'phone', 'address', 'website',
        'lock_results_for_guardians', 'lock_results_only_overdue',
    ];

    protected $casts = [
        'lock_results_for_guardians' => 'boolean',
        'lock_results_only_overdue'  => 'boolean',
    ];
}