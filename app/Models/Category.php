<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Category extends Model
{
    use BelongsToSchool;

    use HasFactory;

    // Allow mass assignment on 'name'
    protected $fillable = [
        'school_id','name'];
}
