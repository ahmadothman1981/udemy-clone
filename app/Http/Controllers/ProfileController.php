<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get public profile by user ID or username
     */
    public function show($identifier)
    {
        $user = is_numeric($identifier)
            ? User::find($identifier)
            : User::where('username', $identifier)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Get badges
        $badges = $user->badges()->get();

        // Get public stats
        $stats = [
            'courses_completed' => $user->enrollments()->whereNotNull('completed_at')->count(),
            'reviews_given' => $user->reviews()->count(),
            'member_since' => $user->created_at->format('F Y'),
        ];

        // If instructor, get instructor stats
        $instructorStats = null;
        if ($user->courses()->exists()) {
            $instructorStats = [
                'courses_created' => $user->courses()->count(),
                'total_students' => $user->courses()->withCount('enrollments')->get()->sum('enrollments_count'),
                'average_rating' => round($user->courses()->avg('rating_avg') ?? 0, 1),
            ];
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'bio' => $user->bio ?? '',
                'headline' => $user->headline ?? '',
            ],
            'badges' => $badges,
            'stats' => $stats,
            'instructor_stats' => $instructorStats,
        ]);
    }

    /**
     * Update own profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'headline' => 'nullable|string|max:100',
            'avatar' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated',
            'user' => $user,
        ]);
    }

    /**
     * Get user's earned badges
     */
    public function badges(Request $request)
    {
        $user = $request->user();

        $earnedBadges = $user->badges()->get();
        $allBadges = Badge::all();

        // Show progress toward unearned badges
        $badgesWithProgress = $allBadges->map(function ($badge) use ($user, $earnedBadges) {
            $earned = $earnedBadges->contains('id', $badge->id);
            $progress = 0;

            if (!$earned) {
                switch ($badge->criteria_type) {
                    case 'courses_completed':
                        $current = $user->enrollments()->whereNotNull('completed_at')->count();
                        $progress = min(100, ($current / $badge->criteria_value) * 100);
                        break;
                    case 'reviews_given':
                        $current = $user->reviews()->count();
                        $progress = min(100, ($current / $badge->criteria_value) * 100);
                        break;
                }
            }

            return [
                'badge' => $badge,
                'earned' => $earned,
                'earned_at' => $earned ? $earnedBadges->find($badge->id)->pivot->earned_at : null,
                'progress' => round($progress),
            ];
        });

        return response()->json($badgesWithProgress);
    }
}
