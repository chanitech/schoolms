<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Department;
use App\Models\JobCard;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property int $department_id
 * @property string|null $position
 * @property string|null $photo
 * @property string $role
 * @property int $user_id
 * @property User $user
 * @property Department $department
 */
class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'position',
        'photo',
        'role',
        'user_id',
    ];

    /**
     * Full name accessor.
     */
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Department relationship.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Linked user relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * JobCards assigned to this staff (they need to complete these).
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
     * === Custom Role Helpers ===
     */

    public function isAssigner(): bool
    {
        return $this->role === 'assigner';
    }

    public function isAssignee(): bool
    {
        return $this->role === 'assignee';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * === New Helpers for Leaves/Approval ===
     */

    public function isHod(): bool
    {
        return $this->role === 'hod';
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
    }
}
