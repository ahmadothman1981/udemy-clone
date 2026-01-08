<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReviewController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(Request $request, Course $course)
    {
        $reviews = $course->reviews()->with('user')->latest()->paginate(10);
        return response()->json($reviews);
    }

    public function store(Request $request, Course $course)
    {
        // Must be enrolled
        $user = $request->user();
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You must be enrolled to review this course.'], 403);
        }

        // One review per user per course
        if ($course->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this course.'], 409);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10',
        ]);

        $review = $course->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        // Update Course Average Rating
        $avg = $course->reviews()->avg('rating');
        $course->update(['rating_avg' => $avg]);

        return response()->json($review, 201);
    }
}
