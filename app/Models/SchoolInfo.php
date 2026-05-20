<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInfo extends Model
{
    protected $table = 'school_infos';

    protected $fillable = [
        'name', 'motto', 'logo', 'email', 'phone', 'address', 'website',
    ];
}