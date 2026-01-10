<?php

namespace App\Http\Controllers;

use App\Http\Resources\LectureResource;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lecture;
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
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:512000', // 500MB limit
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
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:512000',
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
}
