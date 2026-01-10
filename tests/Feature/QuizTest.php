<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $instructor;
    protected Course $course;
    protected Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::factory()->create();
        $this->user = User::factory()->create();

        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
        ]);

        // Create a lecture for the quiz
        $section = $this->course->sections()->create(['title' => 'Test Section', 'position' => 1]);
        $lecture = $section->lectures()->create(['title' => 'Test Lecture', 'position' => 1]);

        $this->quiz = Quiz::create([
            'lecture_id' => $lecture->id,
            'title' => 'Test Quiz',
            'pass_percentage' => 70,
        ]);

        // Add questions
        Question::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'What is 2+2?',
            'options' => ['3', '4', '5', '6'],
            'correct_answer' => '4',
            'points' => 1,
        ]);

        Question::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Is PHP a programming language?',
            'options' => ['True', 'False'],
            'correct_answer' => 'True',
            'points' => 1,
        ]);
    }

    public function test_enrolled_user_can_view_quiz()
    {
        // Enroll user
        Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/courses/{$this->course->id}/quizzes/{$this->quiz->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'quiz' => ['id', 'title', 'pass_percentage', 'questions'],
                'attempts',
                'best_score',
                'passed',
            ]);
    }

    public function test_non_enrolled_user_cannot_view_quiz()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/courses/{$this->course->id}/quizzes/{$this->quiz->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_submit_quiz()
    {
        Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        $questions = $this->quiz->questions;

        $response = $this->actingAs($this->user)
            ->postJson("/api/courses/{$this->course->id}/quizzes/{$this->quiz->id}/submit", [
                'answers' => [
                    ['question_id' => $questions[0]->id, 'answer' => '4'],
                    ['question_id' => $questions[1]->id, 'answer' => 'True'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'attempt_id',
                'score',
                'total_points',
                'percentage',
                'passed',
                'results',
            ]);

        $this->assertTrue($response->json('passed'));
        $this->assertEquals(100, $response->json('percentage'));
    }

    public function test_quiz_fails_with_wrong_answers()
    {
        Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        $questions = $this->quiz->questions;

        $response = $this->actingAs($this->user)
            ->postJson("/api/courses/{$this->course->id}/quizzes/{$this->quiz->id}/submit", [
                'answers' => [
                    ['question_id' => $questions[0]->id, 'answer' => '3'],
                    ['question_id' => $questions[1]->id, 'answer' => 'False'],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('passed'));
        $this->assertEquals(0, $response->json('percentage'));
    }
}
