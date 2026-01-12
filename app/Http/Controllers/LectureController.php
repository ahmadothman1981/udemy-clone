<?php

namespace App\Http\Controllers;

use App\Http\Resources\LectureResource;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lecture;
use App\Models\LectureResource as LectureResourceModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class LectureController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get the storage disk for videos (s3 or public)
     */
    private function getVideoDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }

    /**
     * Generate a video URL (signed for S3, public URL for local)
     */
    private function getVideoUrl(string $path): string
    {
        $disk = $this->getVideoDisk();

        if ($disk === 's3') {
            // Generate a temporary signed URL valid for 2 hours
            return Storage::disk('s3')->temporaryUrl($path, now()->addHours(2));
        }

        // For local storage, return public URL
        return Storage::disk('public')->url($path);
    }

    public function store(Request $request, Course $course, Section $section)
    {
        if ($section->course_id !== $course->id)
            abort(404);
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article,quiz,resource',
            'content' => 'nullable|string', // Text content for articles
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm,mpeg,mpg,m4v|max:512000', // 500MB limit - more flexible format support
            'duration_minutes' => 'nullable|integer',
            'preview' => 'boolean',
        ]);

        $lectureData = $request->only(['title', 'type', 'content', 'duration_minutes', 'preview']);

        // Handle Video Upload
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $disk = $this->getVideoDisk();
            $path = $request->file('video')->store('lectures/' . $course->id, $disk);

            // Store the path (not URL) so we can generate signed URLs later
            $lectureData['video_path'] = $path;
            $lectureData['video_url'] = $this->getVideoUrl($path);
        }

        $lecture = $section->lectures()->create($lectureData);

        return new LectureResource($lecture);
    }

    public function update(Request $request, Course $course, Section $section, Lecture $lecture)
    {
        if ($section->course_id !== $course->id || $lecture->section_id !== $section->id)
            abort(404);
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm,mpeg,mpg,m4v|max:512000', // More flexible format support
            'duration_minutes' => 'nullable|integer',
            'preview' => 'boolean',
            'order' => 'integer',
        ]);

        $lectureData = $request->except('video');

        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $disk = $this->getVideoDisk();

            // Delete old video if exists
            if ($lecture->video_path) {
                Storage::disk($disk)->delete($lecture->video_path);
            }

            $path = $request->file('video')->store('lectures/' . $course->id, $disk);
            $lectureData['video_path'] = $path;
            $lectureData['video_url'] = $this->getVideoUrl($path);
        }

        $lecture->update($lectureData);

        return new LectureResource($lecture);
    }

    public function destroy(Course $course, Section $section, Lecture $lecture)
    {
        if ($section->course_id !== $course->id || $lecture->section_id !== $section->id)
            abort(404);
        $this->authorize('update', $course);

        // Delete video file if exists
        if ($lecture->video_path) {
            $disk = $this->getVideoDisk();
            Storage::disk($disk)->delete($lecture->video_path);
        }

        $lecture->delete();
        return response()->noContent();
    }

    // ==================== RESOURCE MANAGEMENT ====================

    /**
     * Get resources for a lecture
     */
    public function getResources(Course $course, Section $section, Lecture $lecture)
    {
        if ($section->course_id !== $course->id || $lecture->section_id !== $section->id)
            abort(404);

        $resources = $lecture->resources()->get();

        return response()->json($resources);
    }

    /**
     * Upload a resource to a lecture
     */
    public function storeResource(Request $request, Course $course, Section $section, Lecture $lecture)
    {
        if ($section->course_id !== $course->id || $lecture->section_id !== $section->id)
            abort(404);
        $this->authorize('update', $course);

        $request->validate([
            'file' => 'required|file|max:102400', // 100MB limit
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('resources/' . $lecture->id, 'public');

        $resource = LectureResourceModel::create([
            'lecture_id' => $lecture->id,
            'title' => $request->input('title', $file->getClientOriginalName()),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json($resource, 201);
    }

    /**
     * Delete a resource
     */
    public function destroyResource(Request $request, Course $course, Section $section, Lecture $lecture, LectureResourceModel $resource)
    {
        if ($resource->lecture_id !== $lecture->id)
            abort(404);
        $this->authorize('update', $course);

        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();

        return response()->noContent();
    }

    /**
     * Download a resource (for enrolled students)
     */
    public function downloadResource(Request $request, Lecture $lecture, LectureResourceModel $resource)
    {
        if ($resource->lecture_id !== $lecture->id)
            abort(404);

        // Check enrollment (lecture -> section -> course)
        $course = $lecture->section->course;
        $user = $request->user();

        $isEnrolled = $course->isEnrolledBy($user);
        $isInstructor = $course->instructor_id === $user->id;

        if (!$isEnrolled && !$isInstructor) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        // Increment download count
        $resource->increment('downloads');

        $path = Storage::disk('public')->path($resource->file_path);

        return response()->download($path, $resource->file_name);
    }
}

