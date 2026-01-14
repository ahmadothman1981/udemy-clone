<?php

namespace Tests\Feature;

use App\Events\CourseSubmitted;
use App\Models\Course;
use App\Models\User;
use App\Notifications\NewCourseSubmissionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CourseSubmissionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_submission_triggers_event_and_notification()
    {
        Event::fake();
        Notification::fake();

        // 1. Setup Data
        $instructor = User::factory()->create();
        $instructorRole = \App\Models\Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $instructor->roles()->attach($instructorRole);

        // Ensure there is an admin
        $admin = User::factory()->create();
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->roles()->attach($adminRole);

        // Setup Dependencies
        $category = \App\Models\Category::factory()->create();
        $level = \App\Models\CourseLevel::create(['name' => 'Beginner']);

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'level_id' => $level->id,
            'status' => Course::STATUS_DRAFT
        ]);

        // 2. Act: Call the submit endpoint
        $response = $this->actingAs($instructor, 'sanctum')
            ->withoutMiddleware()
            ->postJson("/api/instructor/submit-course/{$course->slug}");

        if ($response->status() !== 200) {
            dump('Status: ' . $response->status());
            dump('Allow: ' . $response->headers->get('Allow'));
            dump('Content: ' . $response->content());
        }

        // 3. Assert Response
        $response->assertStatus(200);
        $this->assertEquals(Course::STATUS_PENDING, $course->fresh()->status);

        // 4. Assert Event Dispatched
        Event::assertDispatched(CourseSubmitted::class, function ($event) use ($course) {
            return $event->course->id === $course->id;
        });
    }

    public function test_full_integration_notification_dispatch()
    {
        Notification::fake();

        // Setup
        $instructor = User::factory()->create();
        $instructorRole = \App\Models\Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $instructor->roles()->attach($instructorRole);

        // Admin Setup
        $admin = User::factory()->create();
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->roles()->attach($adminRole);

        // Setup Dependencies
        $category = \App\Models\Category::factory()->create();
        $level = \App\Models\CourseLevel::create(['name' => 'Beginner']);

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'level_id' => $level->id,
            'status' => Course::STATUS_DRAFT
        ]);

        // Act
        $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/instructor/submit-course/{$course->slug}");

        // Assert
        Notification::assertSentTo(
            [$admin],
            NewCourseSubmissionNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels) && in_array('broadcast', $channels);
            }
        );
    }
}
