<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InstructorController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Stats
        $courses = $user->courses_taught; // using relation define in user model 'courses()' or similar

        if (!$courses) {
            return response()->json(['message' => 'Not an instructor'], 403);
        }

        $totalStudents = 0;
        $totalRevenue = 0;
        $totalReviews = 0;
        $globalRatingSum = 0;
        $courseCount = $courses->count();

        foreach ($courses as $course) {
            $totalStudents += $course->enrollment_count;
            $totalRevenue += ($course->price * $course->enrollment_count); // Simplified calculation
            $totalReviews += $course->reviews()->count();
            $globalRatingSum += $course->rating_avg;
        }

        $avgRating = $courseCount > 0 ? $globalRatingSum / $courseCount : 0;

        return response()->json([
            'total_students' => $totalStudents,
            'total_revenue' => $totalRevenue,
            'average_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
            'course_count' => $courseCount,
            // Recent enrollments could go here
        ]);
    }

    public function courses(Request $request)
    {
        $user = $request->user();
        $courses = $user->courses_taught()->withCount('enrollments', 'reviews')->get();
        return response()->json($courses);
    }
}
