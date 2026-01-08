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

    public function index(Request $request)
    {
        // Get courses user is enrolled in
        $user = $request->user();

        $courses = Course::whereHas('enrollments', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with([
                    'instructor', // Load instructor for display
                    'enrollments' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }
                ])->paginate(10);

        // Calculate progress for each? Expensive to do on fly for list.
        // Ideally progress is cached or stored in enrollment.
        // Let's attach a simplified 'progress_percent' attribute ideally.

        // Using resource collection but might need custom response structure for 'my learning'.
        return CourseResource::collection($courses);
    }
}
