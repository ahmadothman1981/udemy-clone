<?php

namespace App\Http\Controllers;

use App\Mail\CourseCompleted;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\UserProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;

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

            // Check if course is now 100% complete
            $this->checkCourseCompletion($user, $course, $enrollment);
        } else {
            UserProgress::where('enrollment_id', $enrollment->id)
                ->where('lecture_id', $lecture->id)
                ->delete();
        }

        return response()->json(['message' => 'Progress updated']);
    }

    /**
     * Check if course is 100% complete and issue certificate
     */
    private function checkCourseCompletion($user, $course, $enrollment)
    {
        // Load all lectures
        $course->load('sections.lectures');
        $totalLectures = $course->sections->sum(fn($s) => $s->lectures->count());

        if ($totalLectures === 0) {
            return;
        }

        $lectureIds = $course->sections->flatMap(fn($s) => $s->lectures->pluck('id'));
        $completedCount = UserProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lecture_id', $lectureIds)
            ->where('completed', true)
            ->count();

        $progress = round(($completedCount / $totalLectures) * 100);

        if ($progress >= 100) {
            // Check if certificate already exists
            $existingCert = Certificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if (!$existingCert) {
                // Auto-generate certificate
                $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'certificate_number' => Certificate::generateCertificateNumber(),
                    'issued_at' => now(),
                ]);

                // Send completion email
                Mail::to($user->email)->queue(new CourseCompleted($user, $course, $certificate));
            }
        }
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

