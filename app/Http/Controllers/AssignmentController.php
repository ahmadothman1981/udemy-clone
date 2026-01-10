<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get assignment details
     */
    public function show(Request $request, Course $course, Assignment $assignment)
    {
        // Check enrollment
        $enrolled = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$enrolled) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'assignment' => $assignment,
            'submission' => $submission,
            'is_submitted' => !is_null($submission),
            'is_graded' => $submission?->isGraded() ?? false,
        ]);
    }

    /**
     * Submit assignment
     */
    public function submit(Request $request, Course $course, Assignment $assignment)
    {
        $user = $request->user();

        // Check enrollment
        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$enrolled) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        // Check if already submitted
        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->isGraded()) {
            return response()->json(['message' => 'Assignment already graded, cannot resubmit'], 400);
        }

        $validated = $request->validate([
            'content' => 'required_without:attachment|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment') && $assignment->attachment_allowed) {
            $attachmentPath = $request->file('attachment')->store('assignments', 'public');
        }

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'content' => $validated['content'] ?? null,
                'attachment_path' => $attachmentPath,
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'submission' => $submission,
        ]);
    }

    /**
     * Grade assignment (instructor only)
     */
    public function grade(Request $request, Course $course, AssignmentSubmission $submission)
    {
        $user = $request->user();

        // Check if instructor
        if ($course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
            'graded_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Assignment graded',
            'submission' => $submission->fresh(),
        ]);
    }

    /**
     * List submissions for an assignment (instructor)
     */
    public function submissions(Request $request, Course $course, Assignment $assignment)
    {
        $user = $request->user();

        if ($course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->with('user:id,name,email')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'assignment' => $assignment,
            'submissions' => $submissions,
            'graded_count' => $submissions->filter->isGraded()->count(),
            'pending_count' => $submissions->reject->isGraded()->count(),
        ]);
    }
}
