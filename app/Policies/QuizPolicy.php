<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Determine if user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        // Admin or course owner via lecture -> section -> course
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        return $user->id === $quiz->lecture->section->course->instructor_id;
    }

    /**
     * Determine if user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    /**
     * Determine if user can view/take the quiz (enrolled students).
     */
    public function view(User $user, Quiz $quiz): bool
    {
        $course = $quiz->lecture->section->course;

        // Instructors/admin can always view
        if ($user->id === $course->instructor_id || $user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        // Enrolled students can view
        return $course->enrollments()->where('user_id', $user->id)->exists();
    }
}
