<?php

namespace App\Http\Controllers;

use App\Models\UserNote;
use App\Models\Course;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class NoteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get notes for a course
     */
    public function index(Request $request, Course $course)
    {
        $notes = UserNote::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->with('lecture:id,title')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notes);
    }

    /**
     * Create a new note
     */
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'lecture_id' => 'nullable|exists:lectures,id',
            'content' => 'required|string|max:5000',
            'video_timestamp' => 'nullable|integer|min:0',
        ]);

        $note = UserNote::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'lecture_id' => $validated['lecture_id'] ?? null,
            'content' => $validated['content'],
            'video_timestamp' => $validated['video_timestamp'] ?? null,
        ]);

        $note->load('lecture:id,title');

        return response()->json($note, 201);
    }

    /**
     * Update a note
     */
    public function update(Request $request, Course $course, UserNote $note)
    {
        // Ensure ownership
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note->update($validated);

        return response()->json($note);
    }

    /**
     * Delete a note
     */
    public function destroy(Request $request, Course $course, UserNote $note)
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }

        $note->delete();

        return response()->noContent();
    }
}
