<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class Book extends Model
{
    use BelongsToSchool;

    use HasFactory;

    // Allow mass assignment only for actual table columns
    protected $fillable = [
        'school_id',
        'title',
        'author',
        'category_id',
        'isbn',
        'quantity',
    ];

    // Add this relationship
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
