<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lending extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',       // polymorphic borrower id
        'borrower_type', // polymorphic borrower type (Student or Staff)
        'quantity',      // number of books borrowed
        'lend_date',
        'return_date',
        'returned',      // boolean to track if book has been returned
    ];

    protected $casts = [
        'lend_date' => 'date',
        'return_date' => 'date',
        'returned' => 'boolean',
    ];

    /**
     * Relationship to the Book being lent
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Polymorphic borrower relationship (Student or Staff)
     */
    public function borrower(): MorphTo
    {
        return $this->morphTo('borrower', 'borrower_type', 'user_id');
    }

    /**
     * Scope to only get lendings not returned
     */
    public function scopeNotReturned($query)
    {
        return $query->where('returned', false);
    }

    /**
     * Scope to only get returned lendings
     */
    public function scopeReturned($query)
    {
        return $query->where('returned', true);
    }

    /**
     * Check if lending is returned
     */
    public function isReturned(): bool
    {
        return $this->returned;
    }
}
