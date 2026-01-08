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

        // Get courses where user is instructor - using 'courses' relationship
        $courses = $user->courses; // This is hasMany(Course::class, 'instructor_id')

        // Don't return 403 if no courses - instructor can have 0 courses
        $totalStudents = 0;
        $totalRevenue = 0;
        $totalReviews = 0;
        $globalRatingSum = 0;
        $courseCount = $courses->count();

        foreach ($courses as $course) {
            $totalStudents += $course->enrollment_count ?? 0;
            $totalRevenue += (($course->price ?? 0) * ($course->enrollment_count ?? 0));
            $totalReviews += $course->reviews()->count();
            $globalRatingSum += $course->rating_avg ?? 0;
        }

        $avgRating = $courseCount > 0 ? $globalRatingSum / $courseCount : 0;

        return response()->json([
            'total_students' => $totalStudents,
            'total_revenue' => number_format($totalRevenue, 2),
            'average_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
            'course_count' => $courseCount,
            'monthly_students' => rand(5, 30), // Mock for now
            'monthly_revenue' => number_format(rand(100, 2000), 2),
            'monthly_reviews' => rand(1, 15),
        ]);
    }

    public function courses(Request $request)
    {
        $user = $request->user();
        
        // Using correct relationship 'courses' instead of 'courses_taught'
        $courses = $user->courses()
            ->withCount(['enrollments', 'reviews'])
            ->get()
            ->map(function ($course) {
                $course->revenue = number_format(($course->price ?? 0) * ($course->enrollments_count ?? 0), 2);
                $course->rating_avg = $course->rating_avg ?? 0;
                return $course;
            });
            
        return response()->json($courses);
    }
}
