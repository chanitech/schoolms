<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInfo extends Model
{
    protected $fillable = [
        'name',
        'motto',
        'email',
        'phone',
        'address',
        'website',
        'logo',
        'lock_results_for_guardians',
        'lock_results_only_overdue',
    ];

    protected $casts = [
        'lock_results_for_guardians' => 'boolean',
        'lock_results_only_overdue'  => 'boolean',
    ];
}