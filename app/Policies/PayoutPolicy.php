<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    /**
     * Determine if user can view the payout.
     */
    public function view(User $user, Payout $payout): bool
    {
        // Admin or payout owner
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        return $user->id === $payout->instructor_id;
    }

    /**
     * Determine if user can create a payout request.
     */
    public function create(User $user): bool
    {
        // Only instructors can request payouts
        return $user->roles()->where('name', 'instructor')->exists();
    }
}
