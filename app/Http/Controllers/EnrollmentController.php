<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EnrollmentController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function store(Request $request, Course $course)
    {
        // Check if already enrolled
        if ($course->enrollments()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        // Logic for paid vs free. For now, assuming free mock or direct access.
        // In real app, create Order, PaymentIntent, etc.

        $enrollment = $course->enrollments()->create([
            'user_id' => $request->user()->id,
            'amount_paid' => $course->price, // simplified
            'enrolled_at' => now(),
        ]);

        // Bump enrollment count
        $course->increment('enrollment_count');

        return response()->json(['message' => 'Enrolled successfully', 'enrollment_id' => $enrollment->id], 201);
    }

    public function dashboardStats(Request $request) {
        $user = $request->user();
        
        // 1. Enrolled Courses Count
        $enrolledCount = Enrollment::where('user_id', $user->id)->count();

        // 2. Completed Courses (Assuming UserProgress determines completion)
        // This is complex. For now, let's say a course is completed if all lectures are completed?
        // Or simplified: if we had a 'completed_at' on enrollment, that would be easier.
        // Let's check enrollment for 'completed_at' (it doesn't exist yet in the migration we saw but assuming we might use it or infer)
        // Re-checking migration: we don't have 'completed_at' in enrollment table definition in memory.
        // Let's infer completion from UserProgress vs Total Lectures.
        
        $enrollments = Enrollment::where('user_id', $user->id)->with(['course.sections.lectures', 'progress'])->get();
        
        $completedCoursesCount = 0;
        $totalMinutesLearned = 0;
        
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $allLectures = $course->lectures; // via hasManyThrough
            $completedLectureIds = $enrollment->progress->where('completed', true)->pluck('lecture_id')->toArray();
            
            // Calc hours
            $completedLectures = $allLectures->whereIn('id', $completedLectureIds);
            $totalMinutesLearned += $completedLectures->sum('duration_minutes');
            
            // Calc completion
            if ($allLectures->count() > 0 && count($completedLectureIds) >= $allLectures->count()) {
                $completedCoursesCount++;
            }
        }
        
        $hoursLearned = round($totalMinutesLearned / 60, 1);
        
        // Mock Achievements for now (can be dynamic later)
        $achievements = [
            ['id' => 1, 'name' => 'First Course', 'icon' => '🎓', 'unlocked' => $enrolledCount > 0],
            ['id' => 2, 'name' => 'Fast Learner', 'icon' => '⚡', 'unlocked' => $hoursLearned > 5],
            ['id' => 3, 'name' => 'Master', 'icon' => '👑', 'unlocked' => $completedCoursesCount > 0],
        ];

        return response()->json([
            'enrolled_courses_count' => $enrolledCount,
            'hours_learned' => $hoursLearned,
            'completed_courses_count' => $completedCoursesCount,
            'certificates_count' => $completedCoursesCount, // Assuming 1 cert per completed course
            'achievements' => $achievements,
            'learning_streak' => rand(1, 30), // Mock streak for now
        ]);
    }

    public function index(Request $request)
    {
        // Get courses user is enrolled in
        $user = $request->user();

        $courses = Course::whereHas('enrollments', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with([
            'instructor',
            'sections.lectures' // Need this to count total lectures
        ])->paginate(10);

        // Attach progress to each course
        // We can't easily modify the paginator's collection directly and return it as clean paginator without transforming.
        // So we will use map on the collection.
        
        $courses->getCollection()->transform(function ($course) use ($user) {
            $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
            $totalLectures = $course->lectures->count();
            
            // Get user progress count
            if ($enrollment) {
                 $completedCount = \App\Models\UserProgress::where('enrollment_id', $enrollment->id)
                    ->where('completed', true)
                    ->count();
            } else {
                $completedCount = 0;
            }
            
            $percent = $totalLectures > 0 ? round(($completedCount / $totalLectures) * 100) : 0;
            
            $course->progress = $percent;
            return $course;
        });

        return CourseResource::collection($courses);
    }
}
