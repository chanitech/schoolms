<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Student;

class StudentResultPolicy
{
    /**
     * Determine if the user can view a student's results.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Student $student
     * @return bool
     */
    public function viewResults(User $user, Student $student)
    {
        // Admin, Finance, Teacher – always allowed (no lock restriction for them)
        if ($user->hasRole(['Admin', 'Finance', 'Teacher'])) {
            return true;
        }

        // Guardian – only own children and only if results are not locked
        if ($user->is_guardian && $user->guardian) {
            $hasChild = $user->guardian->students()
                ->where('student_id', $student->id)
                ->exists();
            return $hasChild && !$student->results_locked;
        }

        // Default deny
        return false;
    }

    /**
     * Determine if the user can lock/unlock student results.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function lockResults(User $user)
    {
        return $user->hasRole(['Admin', 'Finance']);
    }
}