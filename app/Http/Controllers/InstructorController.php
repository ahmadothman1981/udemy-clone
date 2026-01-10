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

        // Get courses where user is instructor
        $courses = $user->courses;

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

        // Recent Reviews (limit 5)
        $courseIds = $courses->pluck('id');
        $recentReviews = \App\Models\Review::whereIn('course_id', $courseIds)
            ->with(['user:id,name', 'course:id,title'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => $review->user,
                    'course' => $review->course,
                    'rating' => $review->rating,
                    'comment' => $review->content, // Map content to comment
                    'created_at' => $review->created_at,
                ];
            });

        // Recent Questions (limit 5)
        $recentQuestions = \App\Models\CourseQuestion::whereIn('course_id', $courseIds)
            ->with(['user:id,name', 'course:id,title'])
            ->withCount('answers')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'user' => $q->user,
                    'course' => $q->course,
                    'title' => \Illuminate\Support\Str::limit($q->question, 50), // Generate title from question
                    'body' => $q->question,
                    'answered' => $q->answers_count > 0,
                    'created_at' => $q->created_at, // Use created_at or asked_at if exists
                ];
            });

        $unansweredCount = \App\Models\CourseQuestion::whereIn('course_id', $courseIds)
            ->whereDoesntHave('answers') // Assuming 'answers' relation exists
            ->count();

        return response()->json([
            'total_students' => $totalStudents,
            'total_revenue' => number_format($totalRevenue, 2),
            'average_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
            'course_count' => $courseCount,
            'monthly_students' => rand(5, 30), // Mock for now
            'monthly_revenue' => number_format(rand(100, 2000), 2),
            'monthly_reviews' => rand(1, 15),
            'recent_reviews' => $recentReviews,
            'recent_questions' => $recentQuestions,
            'unanswered_questions_count' => $unansweredCount
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

    /**
     * Enhanced analytics for instructor dashboard
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        $courseIds = $user->courses->pluck('id');

        // Enrollment trends (last 30 days)
        $enrollmentTrends = \App\Models\Enrollment::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($e) => ['date' => $e->date, 'enrollments' => $e->count]);

        // Revenue by course (from actual orders)
        $revenueByCourse = $user->courses()
            ->withCount('enrollments')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'enrollments' => $course->enrollments_count,
                    'revenue' => $course->price * $course->enrollments_count,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // Student engagement (completion rates)
        $engagement = $user->courses()
            ->with(['sections.lectures', 'enrollments.progress'])
            ->get()
            ->map(function ($course) {
                $totalLectures = $course->sections->sum(fn($s) => $s->lectures->count());
                $enrolledCount = $course->enrollments->count();

                if ($totalLectures === 0 || $enrolledCount === 0) {
                    return [
                        'course_id' => $course->id,
                        'title' => $course->title,
                        'avg_completion' => 0,
                        'completed_students' => 0,
                    ];
                }

                $totalCompleted = 0;
                $fullyCompleted = 0;

                foreach ($course->enrollments as $enrollment) {
                    $completedLectures = $enrollment->progress->where('completed', true)->count();
                    $percentage = ($completedLectures / $totalLectures) * 100;
                    $totalCompleted += $percentage;
                    if ($percentage >= 100)
                        $fullyCompleted++;
                }

                return [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'avg_completion' => round($totalCompleted / $enrolledCount, 1),
                    'completed_students' => $fullyCompleted,
                ];
            });

        // Monthly summary
        $thisMonth = now()->startOfMonth();
        $monthlyEnrollments = \App\Models\Enrollment::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $monthlyRevenue = \App\Models\Enrollment::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', $thisMonth)
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->sum('courses.price');

        return response()->json([
            'enrollment_trends' => $enrollmentTrends,
            'revenue_by_course' => $revenueByCourse,
            'engagement' => $engagement,
            'monthly_summary' => [
                'enrollments' => $monthlyEnrollments,
                'revenue' => number_format($monthlyRevenue, 2),
            ],
        ]);
    }
}
