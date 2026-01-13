<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can create models.
     */
    protected function isVerifiedInstructor(User $user): bool
    {
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        if ($user->roles()->where('name', 'instructor')->exists()) {
            return $user->instructor_verification_status === 'approved';
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isVerifiedInstructor($user);
    }

    /**
     * Determine whether the user can view the model (e.g. for editing context).
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }
        return $user->id === $course->instructor_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        return $this->isVerifiedInstructor($user) && $user->id === $course->instructor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        return $this->isVerifiedInstructor($user) && $user->id === $course->instructor_id;
    }
}
