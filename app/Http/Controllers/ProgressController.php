<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\UserProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgressController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function update(Request $request, Lecture $lecture)
    {
        $user = $request->user();

        // Find enrollment
        // Lecture belong to section -> course
        $course = $lecture->section->course;

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Mark complete or incomplete
        $completed = $request->boolean('completed', true);

        if ($completed) {
            UserProgress::firstOrCreate([
                'enrollment_id' => $enrollment->id,
                'lecture_id' => $lecture->id,
            ], [
                'completed' => true,
                'completed_at' => now(),
            ]);
        } else {
            UserProgress::where('enrollment_id', $enrollment->id)
                ->where('lecture_id', $lecture->id)
                ->delete();
        }

        // Recalculate % if we wanted to store it

        return response()->json(['message' => 'Progress updated']);
    }

    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $completedLectureIds = UserProgress::where('enrollment_id', $enrollment->id)
            ->where('completed', true)
            ->pluck('lecture_id');

        return response()->json([
            'completed_lectures' => $completedLectureIds,
        ]);
    }
}
