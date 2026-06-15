<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{
    Guardian,
    SchoolClass,
    Dormitory,
    DormitoryBed,
    DormitoryBedAllocation,
    AcademicSession,
    Enrollment,
    Mark,
    Subject,
    Department
};

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'national_id',
        'photo',
        'guardian_id',
        'class_id',
        //'department_id',
        'dormitory_id',
        'academic_session_id',
        'admission_date',
        'status',
        'address',
        'phone',
        'email',
    ];

    /* ============================
     | 🔹 RELATIONSHIPS
     ============================ */

    // Guardian
    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    // School Class
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Add this alias for backward compatibility
    public function class()
    {
        return $this->schoolClass();
    }

    // Dormitory (current dormitory assignment)
    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    // Academic Session
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    // Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Enrollments
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Marks
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // Marks filtered by exam
    public function marksForExam($examId)
    {
        return $this->marks()->where('exam_id', $examId);
    }

    // Marks filtered by session
    public function marksForSession($sessionId)
    {
        return $this->marks()->where('academic_session_id', $sessionId);
    }

    // Subjects (many-to-many)
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id')
                    ->withPivot('withdrawn')
                    ->withTimestamps();
    }

    // Active Subjects (not withdrawn)
    public function activeSubjects()
    {
        return $this->subjects()->wherePivot('withdrawn', 0);
    }

    // Withdrawn Subjects
    public function withdrawnSubjects()
    {
        return $this->subjects()->wherePivot('withdrawn', 1);
    }

    // ==================== DORMITORY MANAGEMENT RELATIONSHIPS ====================
    
    // Current bed allocation (direct from bed table)
    public function currentBed()
    {
        return $this->belongsTo(DormitoryBed::class, 'id', 'current_student_id');
    }
    
    // Bed allocation history
    public function bedAllocations()
    {
        return $this->hasMany(DormitoryBedAllocation::class, 'student_id');
    }
    
    // Active bed allocation (current active allocation)
    public function activeBedAllocation()
    {
        return $this->hasOne(DormitoryBedAllocation::class, 'student_id')
                    ->where('status', 'active');
    }
    
    // Check if student has active bed allocation
    public function hasBedAllocation()
    {
        return $this->activeBedAllocation()->exists();
    }
    
    // Get current room through bed allocation
    public function currentRoom()
    {
        return $this->hasOneThrough(
            DormitoryRoom::class,
            DormitoryBed::class,
            'current_student_id',
            'id',
            'id',
            'room_id'
        );
    }
    
    // Get bed details with room and dormitory
    public function getBedDetailsAttribute()
    {
        $allocation = $this->activeBedAllocation;
        if (!$allocation) return null;
        
        $bed = $allocation->bed;
        if (!$bed) return null;
        
        $room = $bed->room;
        if (!$room) return null;
        
        $dormitory = $room->dormitory;
        
        return (object)[
            'dormitory' => $dormitory ? $dormitory->name : 'N/A',
            'room' => $room->room_number,
            'bed' => $bed->bed_number,
            'bed_type' => $bed->bed_type,
            'floor' => $room->floor ?? 'Ground',
        ];
    }

    /* ============================
     | 🔹 ACCESSORS
     ============================ */

    public function getFullNameAttribute()
    {
        $names = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $names);
    }
    
    public function getNameAttribute()
    {
        return $this->full_name;
    }
    
    // Get formatted admission number
    public function getFormattedAdmissionNoAttribute()
    {
        return $this->admission_no ?? 'N/A';
    }
    
    // Get student initials
    public function getInitialsAttribute()
    {
        $initials = '';
        if ($this->first_name) $initials .= $this->first_name[0];
        if ($this->last_name) $initials .= $this->last_name[0];
        return strtoupper($initials);
    }
    
    // Get student age
    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) return null;
        return \Carbon\Carbon::parse($this->date_of_birth)->age;
    }
    
    // Get gender label
    public function getGenderLabelAttribute()
    {
        return ucfirst($this->gender);
    }
    
    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'graduated' => 'info',
            'suspended' => 'danger',
            default => 'warning'
        };
    }

    /* ============================
     | 🔹 SCOPES
     ============================ */

    // Filter students by department
    public function scopeOfDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Filter students by active status
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    // Filter students by gender
    public function scopeMale($query)
    {
        return $query->where('gender', 'male');
    }
    
    // Filter students by gender
    public function scopeFemale($query)
    {
        return $query->where('gender', 'female');
    }
    
    // Filter students by dormitory
    public function scopeInDormitory($query, $dormitoryId)
    {
        return $query->where('dormitory_id', $dormitoryId);
    }
    
    // Filter students with bed allocation
    public function scopeWithBedAllocation($query)
    {
        return $query->whereHas('activeBedAllocation');
    }
    
    // Filter students without bed allocation
    public function scopeWithoutBedAllocation($query)
    {
        return $query->whereDoesntHave('activeBedAllocation');
    }
    
    // Search students by name or admission number
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('admission_no', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /* ============================
     | 🔹 RESULTS METHODS
     ============================ */
    
    // Student results
    public function results()
    {
        return $this->marks();
    }
    
    // Get results for specific exam
    public function getResultsForExam($examId)
    {
        return $this->marks()->where('exam_id', $examId)->get();
    }
    
    // Get best subjects based on marks
    public function getBestSubjects($limit = 7)
    {
        return $this->marks()
            ->with('subject')
            ->orderBy('mark', 'desc')
            ->limit($limit)
            ->get();
    }
    
    // Get GPA for specific exam
    public function getGpaForExam($examId)
    {
        $marks = $this->marks()->where('exam_id', $examId)->get();
        if ($marks->isEmpty()) return 0;
        
        $totalPoints = 0;
        foreach ($marks as $mark) {
            $grade = Grade::where('min_mark', '<=', $mark->mark)
                        ->where('max_mark', '>=', $mark->mark)
                        ->first();
            $totalPoints += $grade->point ?? 5;
        }
        
        return round($totalPoints / $marks->count(), 2);
    }

    /* ============================
     | 🔹 HELPER METHODS
     ============================ */
    
    // Check if student can be allocated a bed
    public function canBeAllocated()
    {
        return !$this->hasBedAllocation() && $this->status === 'active';
    }
    
    // Get current academic session enrollment
    public function getCurrentEnrollment()
    {
        $currentSession = AcademicSession::where('is_current', true)->first();
        if (!$currentSession) return null;
        
        return $this->enrollments()
            ->where('academic_session_id', $currentSession->id)
            ->where('status', 'active')
            ->first();
    }
    
    // Check if student is enrolled in current session
    public function isEnrolledInCurrentSession()
    {
        return $this->getCurrentEnrollment() !== null;
    }


    public function studentBills()
{
    return $this->hasMany(\App\Models\StudentBill::class, 'student_id');
}

public function payments()
{
    return $this->hasMany(\App\Models\Payment::class, 'student_id');
}

public function pocketTransactions()
{
    return $this->hasMany(\App\Models\PocketTransaction::class, 'student_id');
}
}