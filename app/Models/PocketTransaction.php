<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchool;

class PocketTransaction extends Model
{
    use BelongsToSchool;

    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'type',
        'amount',
        'balance_after',
        'performed_by',
        'note',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
