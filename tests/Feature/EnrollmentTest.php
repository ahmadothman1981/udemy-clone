<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\CourseLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $instructor;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $studentRole = Role::create(['name' => 'student']);
        $instructorRole = Role::create(['name' => 'instructor']);

        // Create users
        $this->student = User::factory()->create();
        $this->student->roles()->attach($studentRole);

        $this->instructor = User::factory()->create();
        $this->instructor->roles()->attach($instructorRole);

        // Create category and level
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);
        $level = CourseLevel::create(['name' => 'Beginner']);

        // Create course
        $this->course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'A test course',
            'price' => 49.99,
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'level_id' => $level->id,
            'language' => 'English',
            'published' => true,
        ]);
    }

    public function test_user_can_enroll_in_course(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/courses/{$this->course->id}/enroll");

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'enrollment_id']);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    public function test_user_cannot_enroll_twice(): void
    {
        // First enrollment
        Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount_paid' => $this->course->price,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($this->student)
            ->postJson("/api/courses/{$this->course->id}/enroll");

        $response->assertStatus(409);
    }

    public function test_guest_cannot_enroll(): void
    {
        $response = $this->postJson("/api/courses/{$this->course->id}/enroll");

        $response->assertStatus(401);
    }

    public function test_enrollment_creates_instructor_earning(): void
    {
        $this->actingAs($this->student)
            ->postJson("/api/courses/{$this->course->id}/enroll");

        $this->assertDatabaseHas('instructor_earnings', [
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
        ]);
    }
}
