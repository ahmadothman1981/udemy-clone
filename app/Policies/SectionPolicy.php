<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    /**
     * Determine if user can update the section.
     */
    public function update(User $user, Section $section): bool
    {
        // Admin or course owner
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        return $user->id === $section->course->instructor_id;
    }

    /**
     * Determine if user can delete the section.
     */
    public function delete(User $user, Section $section): bool
    {
        return $this->update($user, $section);
    }
}
