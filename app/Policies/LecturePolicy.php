<?php

namespace App\Policies;

use App\Models\Lecture;
use App\Models\User;

class LecturePolicy
{
    /**
     * Determine if user can update the lecture.
     */
    public function update(User $user, Lecture $lecture): bool
    {
        // Admin or course owner
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        return $user->id === $lecture->section->course->instructor_id;
    }

    /**
     * Determine if user can delete the lecture.
     */
    public function delete(User $user, Lecture $lecture): bool
    {
        return $this->update($user, $lecture);
    }
}
