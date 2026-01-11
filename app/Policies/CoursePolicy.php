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
    public function create(User $user): bool
    {
        // Only instructors can create courses
        return $user->roles()->where('name', 'instructor')->exists() || $user->roles()->where('name', 'admin')->exists();
    }

    /**
     * Determine whether the user can view the model (e.g. for editing context).
     */
    public function view(User $user, Course $course): bool
    {
        // Admin or owner
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
        // Admin or owner
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }
        return $user->id === $course->instructor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        // Admin or owner
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }
        return $user->id === $course->instructor_id;
    }
}
