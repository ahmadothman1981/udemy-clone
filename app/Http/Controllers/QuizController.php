<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture; // Quizzes are tied to lectures, or specialized lecture types
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuizController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function store(Request $request, Course $course, Lecture $lecture)
    {
        // Lecture must belong to course flow
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string',
            'pass_percentage' => 'integer|min:0|max:100',
            'time_limit' => 'nullable|integer',
        ]);

        $validated['lecture_id'] = $lecture->id;

        $quiz = Quiz::create($validated);

        return response()->json($quiz, 201);
    }

    public function show(Course $course, Quiz $quiz)
    {
        // Check access... user enrolled or instructor
        $quiz->load('questions');
        return response()->json($quiz);
    }

    public function storeQuestion(Request $request, Course $course, Quiz $quiz)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'points' => 'integer|min:1',
        ]);

        $question = $quiz->questions()->create($validated);

        return response()->json($question, 201);
    }
}
