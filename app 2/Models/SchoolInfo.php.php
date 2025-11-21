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
        'logo',
    ];
}
