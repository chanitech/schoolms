<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\Staff;

class Dormitory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'capacity',
        'gender',
        'dorm_master_id',
    ];

    // Students in this dormitory
    public function students()
    {
        return $this->hasMany(Student::class, 'dormitory_id');
    }

    // Dorm master (staff)
    public function dormMaster()
    {
        return $this->belongsTo(Staff::class, 'dorm_master_id');
    }
}
