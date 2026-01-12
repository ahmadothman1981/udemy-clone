<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Enrollment;
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

    /**
     * Create a new quiz for a lecture
     */
    public function store(Request $request, Course $course, Lecture $lecture)
    {
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

    /**
     * Update an existing quiz
     */
    public function update(Request $request, Course $course, Quiz $quiz)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'pass_percentage' => 'sometimes|integer|min:0|max:100',
            'time_limit' => 'nullable|integer',
        ]);

        $quiz->update($validated);

        return response()->json($quiz);
    }

    /**
     * Get quiz with questions for taking
     */
    public function show(Course $course, Quiz $quiz)
    {
        $user = request()->user();

        // Check if user is enrolled or is instructor
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        $isInstructor = $course->instructor_id === $user->id;

        if (!$isEnrolled && !$isInstructor) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $quiz->load([
            'questions' => function ($query) use ($isInstructor) {
                if ($isInstructor) {
                    // Instructor sees everything
                    $query->select('*');
                } else {
                    // Don't send correct answers to frontend for students
                    $query->select('id', 'quiz_id', 'question_text', 'options', 'points');
                }
            }
        ]);

        // Get user's previous attempts
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'quiz' => $quiz,
            'attempts' => $attempts,
            'best_score' => $attempts->max('score'),
            'passed' => $attempts->where('passed', true)->count() > 0,
            'is_instructor' => $isInstructor
        ]);
    }

    /**
     * Add a question to a quiz
     */
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

    /**
     * Update an existing question
     */
    public function updateQuestion(Request $request, Course $course, Quiz $quiz, Question $question)
    {
        $this->authorize('update', $course);

        // Ensure question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'options' => 'sometimes|array|min:2',
            'correct_answer' => 'sometimes|string',
            'points' => 'integer|min:1',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    /**
     * Delete a question
     */
    public function destroyQuestion(Request $request, Course $course, Quiz $quiz, Question $question)
    {
        $this->authorize('update', $course);

        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $question->delete();

        return response()->noContent();
    }

    /**
     * Submit quiz answers and get results
     */
    public function submit(Request $request, Course $course, Quiz $quiz)
    {
        $user = $request->user();

        // Check enrollment
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|string',
            'started_at' => 'nullable|date',
        ]);

        // Load quiz with questions
        $quiz->load('questions');

        // Calculate score
        $score = 0;
        $totalPoints = 0;
        $results = [];

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points ?? 1;

            // Find user's answer for this question
            $userAnswer = collect($validated['answers'])
                ->firstWhere('question_id', $question->id);

            $isCorrect = false;
            if ($userAnswer && strtolower(trim($userAnswer['answer'])) === strtolower(trim($question->correct_answer))) {
                $isCorrect = true;
                $score += $question->points ?? 1;
            }

            $results[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'user_answer' => $userAnswer['answer'] ?? null,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'points' => $question->points ?? 1,
            ];
        }

        // Calculate percentage and pass/fail
        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100) : 0;
        $passed = $percentage >= $quiz->pass_percentage;

        // Save attempt
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_points' => $totalPoints,
            'passed' => $passed,
            'answers' => $validated['answers'],
            'started_at' => $validated['started_at'] ?? now(),
            'completed_at' => now(),
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $passed,
            'pass_percentage' => $quiz->pass_percentage,
            'results' => $results,
            'message' => $passed ? 'Congratulations! You passed!' : 'Keep practicing and try again!',
        ]);
    }

    /**
     * Get user's attempts for a quiz
     */
    public function attempts(Request $request, Course $course, Quiz $quiz)
    {
        $user = $request->user();

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'attempts' => $attempts,
            'best_score' => $attempts->max('score'),
            'total_attempts' => $attempts->count(),
            'passed' => $attempts->where('passed', true)->count() > 0,
        ]);
    }
}
