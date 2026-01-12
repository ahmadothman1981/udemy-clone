<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\CourseLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $studentRole = Role::create(['name' => 'student']);
        $instructorRole = Role::create(['name' => 'instructor']);

        // Create users
        $this->user = User::factory()->create();
        $this->user->roles()->attach($studentRole);

        $instructor = User::factory()->create();
        $instructor->roles()->attach($instructorRole);

        // Create category and level
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);
        $level = CourseLevel::create(['name' => 'Beginner']);

        // Create course
        $this->course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'A test course',
            'price' => 49.99,
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'level_id' => $level->id,
            'language' => 'English',
            'published' => true,
        ]);
    }

    public function test_checkout_preview_returns_course_details(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/checkout/preview', [
                'course_ids' => [$this->course->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['items', 'subtotal', 'total']);
    }

    public function test_checkout_preview_requires_auth(): void
    {
        $response = $this->postJson('/api/checkout/preview', [
            'course_ids' => [$this->course->id],
        ]);

        $response->assertStatus(401);
    }

    public function test_checkout_requires_course_ids(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/checkout/preview', []);

        $response->assertStatus(422);
    }

    public function test_free_checkout_creates_enrollment(): void
    {
        // Create a free course
        $freeCourse = Course::create([
            'title' => 'Free Course',
            'slug' => 'free-course',
            'description' => 'A free course',
            'price' => 0,
            'instructor_id' => $this->course->instructor_id,
            'category_id' => $this->course->category_id,
            'level_id' => $this->course->level_id,
            'language' => 'English',
            'published' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/checkout', [
                'course_ids' => [$freeCourse->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'course_id' => $freeCourse->id,
        ]);
    }
}
