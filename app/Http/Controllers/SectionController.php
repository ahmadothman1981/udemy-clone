<?php

namespace App\Http\Controllers;

use App\Http\Resources\SectionResource;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SectionController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function index(Course $course)
    {
        // Publicly visible if permitted, but usually part of Course details. 
        // This endpoint might be for instructor overview.
        $this->authorize('view', $course); // Ensure user can view (e.g. is instructor or admin or enrolled student?)
        // Actually, for editing curriculum, strict policy.

        $sections = $course->sections()->with('lectures')->orderBy('order')->get();
        return SectionResource::collection($sections);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course); // Use course update policy

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'integer|min:0',
        ]);

        $section = $course->sections()->create($validated);

        return new SectionResource($section);
    }

    public function update(Request $request, Course $course, Section $section)
    {
        // Ensure section belongs to course
        if ($section->course_id !== $course->id) {
            abort(404);
        }

        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:0',
        ]);

        $section->update($validated);

        return new SectionResource($section);
    }

    public function destroy(Course $course, Section $section)
    {
        if ($section->course_id !== $course->id) {
            abort(404);
        }
        $this->authorize('update', $course); // Deleting section is updating course structure

        $section->delete();

        return response()->noContent();
    }

    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:sections,id',
            'sections.*.order' => 'required|integer',
        ]);

        foreach ($request->sections as $item) {
            $section = Section::where('course_id', $course->id)->find($item['id']);
            if ($section) {
                $section->update(['order' => $item['order']]);
            }
        }

        return response()->json(['message' => 'Sections reordered successfully']);
    }
}
