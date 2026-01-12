<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine if user can update the review.
     */
    public function update(User $user, Review $review): bool
    {
        // Admin or review author
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        return $user->id === $review->user_id;
    }

    /**
     * Determine if user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $this->update($user, $review);
    }
}
