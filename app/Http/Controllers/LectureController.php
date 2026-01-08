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

    public function store(Request $request, Course $course, Section $section)
    {
        if ($section->course_id !== $course->id)
            abort(404);
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article,quiz,resource',
            'content' => 'nullable|string', // Text content for articles
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:102400', // 100MB limit for demo
            'duration_minutes' => 'nullable|integer',
            'preview' => 'boolean',
        ]);

        $lectureData = $request->only(['title', 'type', 'content', 'duration_minutes', 'preview']);

        // Handle Video Upload
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $path = $request->file('video')->store('lectures', 'public');
            $lectureData['video_url'] = Storage::url($path);
            // In a real app, we'd process duration here or use a service like Vimeo/AWS MediaConvert
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
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:102400',
            'duration_minutes' => 'nullable|integer',
            'preview' => 'boolean',
            'order' => 'integer',
        ]);

        $lectureData = $request->except('video');

        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            // Delete old video if exists?
            // if ($lecture->video_url) ...

            $path = $request->file('video')->store('lectures', 'public');
            $lectureData['video_url'] = Storage::url($path);
        }

        $lecture->update($lectureData);

        return new LectureResource($lecture);
    }

    public function destroy(Course $course, Section $section, Lecture $lecture)
    {
        if ($section->course_id !== $course->id || $lecture->section_id !== $section->id)
            abort(404);
        $this->authorize('update', $course);

        $lecture->delete();
        return response()->noContent();
    }
}
