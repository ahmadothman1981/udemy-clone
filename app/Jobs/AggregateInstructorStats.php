<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateInstructorStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     * Pre-aggregate instructor stats for fast dashboard loading.
     */
    public function handle(): void
    {
        // Get all instructors (users with courses)
        $instructors = User::whereHas('courses')->get();

        $thisMonth = now()->startOfMonth();
        $today = now()->toDateString();

        foreach ($instructors as $instructor) {
            $courses = $instructor->courses;
            $courseIds = $courses->pluck('id');

            // Compute stats
            $totalStudents = $courses->sum('enrollment_count');
            $totalRevenue = $courses->sum(fn($c) => ($c->price ?? 0) * ($c->enrollment_count ?? 0));
            $totalReviews = Review::whereIn('course_id', $courseIds)->count();
            $courseCount = $courses->count();

            // Average rating
            $avgRating = $courseCount > 0
                ? $courses->avg('rating_avg') ?? 0
                : 0;

            // Monthly stats
            $monthlyStudents = Enrollment::whereIn('course_id', $courseIds)
                ->where('created_at', '>=', $thisMonth)
                ->count();

            $monthlyRevenue = Enrollment::whereIn('enrollments.course_id', $courseIds)
                ->where('enrollments.created_at', '>=', $thisMonth)
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->sum('courses.price');

            $monthlyReviews = Review::whereIn('course_id', $courseIds)
                ->where('created_at', '>=', $thisMonth)
                ->count();

            $unansweredQuestions = CourseQuestion::whereIn('course_id', $courseIds)
                ->whereDoesntHave('answers')
                ->count();

            // Upsert stats
            DB::table('instructor_stats')->updateOrInsert(
                [
                    'instructor_id' => $instructor->id,
                    'stats_date' => $today,
                ],
                [
                    'total_students' => $totalStudents,
                    'total_revenue' => round($totalRevenue, 2),
                    'average_rating' => round($avgRating, 2),
                    'total_reviews' => $totalReviews,
                    'course_count' => $courseCount,
                    'monthly_students' => $monthlyStudents,
                    'monthly_revenue' => round($monthlyRevenue, 2),
                    'monthly_reviews' => $monthlyReviews,
                    'unanswered_questions' => $unansweredQuestions,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
