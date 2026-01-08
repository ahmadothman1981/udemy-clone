<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_platform_workflow()
    {
        // 1. Setup Data
        Role::create(['name' => 'student']);
        Role::create(['name' => 'instructor']);
        Role::create(['name' => 'admin']);

        $category = Category::factory()->create();
        $level = CourseLevel::factory()->create();
        // Create Role if using Spatie or simple model
        // Assuming simple model based on conversation

        // 2. Instructor Registration
        $instructorData = [
            'name' => 'Instructor John',
            'email' => 'instructor@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'instructor' // Assuming registration handles this or default
        ];

        $response = $this->postJson('/api/register', $instructorData);
        $response->assertStatus(201);
        $token = $response->json('token');

        // 3. Create Course (as Instructor)
        $courseData = [
            'title' => 'Master Vue 3',
            'description' => 'Complete guide',
            'price' => 99.99,
            'category_id' => $category->id,
            'level_id' => $level->id,
            'language' => 'English'
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/courses', $courseData);
        $response->assertStatus(201);
        $courseId = $response->json('data.id');

        // 4. Admin Approval
        $admin = User::factory()->create(['email' => 'admin@test.com']);
        $adminToken = $admin->createToken('admin')->plainTextToken;
        // Mock admin role check if implemented

        $response = $this->withHeader('Authorization', "Bearer $adminToken")
            ->postJson("/api/admin/courses/$courseId/approve", ['action' => 'approve']);
        $response->assertStatus(200);
        // If route is protected by ability, this might fail without setup, but let's test logic.
        // Assuming middleware allows actingAs to bypass token or we generate token for admin

        $this->assertDatabaseHas('courses', ['id' => $courseId, 'status' => 'published']);

        // 5. Student Enrollment
        $student = User::factory()->create(['email' => 'student@test.com']);
        $studentToken = $student->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $studentToken")
            ->postJson("/api/courses/$courseId/enroll");
        $response->assertStatus(201);

        // 6. Social Review
        $reviewData = ['rating' => 5, 'content' => 'Great course!'];
        $response = $this->withHeader('Authorization', "Bearer $studentToken")
            ->postJson("/api/courses/$courseId/reviews", $reviewData);
        $response->assertStatus(201);

        // 7. Verify Stats
        $this->assertDatabaseHas('reviews', ['content' => 'Great course!']);
        $this->assertEquals(5, Course::find($courseId)->rating_avg);
    }
}
