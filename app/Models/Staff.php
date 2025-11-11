<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Department;
use App\Models\JobCard;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property int $department_id
 * @property string|null $position
 * @property string|null $photo
 * @property int $user_id
 * @property User $user
 * @property Department $department
 */
class Staff extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    // Make sure Spatie knows the guard
    protected $guard_name = 'web';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'position',
        'photo',
        'user_id',
    ];

    /**
     * Accessor for full name.
     */
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Accessor for primary Spatie role name.
     */
    public function getRoleNameAttribute(): string
    {
        return $this->roles->pluck('name')->first() ?? 'Staff';
    }

    /**
     * Department relationship.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Linked User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * JobCards assigned to this staff (tasks they need to complete).
     */
    public function jobcards()
    {
        return $this->hasMany(JobCard::class, 'assigned_to');
    }

    /**
     * JobCards assigned by this staff (tasks they created for others).
     */
    public function assignedJobcards()
    {
        return $this->hasMany(JobCard::class, 'assigned_by');
    }

    /**
     * Calculate average rating for completed tasks.
     */
    public function averageRating(): ?float
    {
        return $this->jobcards()
            ->whereNotNull('rating')
            ->avg('rating');
    }

    /**
     * === Role helper methods using Spatie ===
     */
    public function isAssigner(): bool
    {
        return $this->hasRole('assigner');
    }

    public function isAssignee(): bool
    {
        return $this->hasRole('assignee');
    }

    public function isHod(): bool
    {
        return $this->hasRole('hod');
    }

    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }
}
