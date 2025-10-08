<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'head_id'
    ];

    public function head()
    {
        return $this->belongsTo(Staff::class, 'head_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
