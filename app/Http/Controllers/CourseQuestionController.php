<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseQuestionController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', only: ['store', 'storeAnswer']),
        ];
    }

    public function index(Request $request, Course $course)
    {
        $questions = $course->questions()
            ->with(['user', 'answers.user'])
            ->latest()
            ->paginate(10);

        return response()->json($questions);
    }

    public function store(Request $request, Course $course)
    {
        // Should ideally check enrollment or instructor status

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'lecture_id' => 'nullable|exists:lectures,id', // Optional context
        ]);

        $question = $course->questions()->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'lecture_id' => $validated['lecture_id'] ?? null,
        ]);

        // Fire Event for Real-time Notification to Instructor
        // event(new QuestionAsked($question));

        return response()->json($question, 201);
    }

    public function storeAnswer(Request $request, Course $course, CourseQuestion $question)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return response()->json($answer, 201);
    }
}
