<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class JobDescription extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'role_name',
        'description',
        'updated_by',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
