<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles if they don't exist (basic setup)
        if (Role::count() == 0) {
            Role::create(['name' => 'student']);
            Role::create(['name' => 'instructor']);
            Role::create(['name' => 'admin']);
        }
    }

    public function test_user_email_verified_cannot_be_mass_assigned()
    {
        $user = User::factory()->create([
            'email_verified' => false,
        ]);

        $this->actingAs($user);

        // Attempt to update profile with email_verified set to true
        // Assuming we have a profile update endpoint that uses User::update($validated)
        // Check AuthController::updateProfile logic first. 
        // If AuthController uses explicit assignment ($user->name = ...), mass assignment protection might not apply there directly
        // unless it uses $user->update($request->all()) or similar.
        // However, the vulnerability was flagged in the Model $fillable, so we test Model behavior directly as well as controller if applicable.

        // Direct Model Test
        $user->update(['email_verified' => true]);
        $this->assertFalse((bool) $user->fresh()->email_verified, 'email_verified should not be updateable via mass assignment');
    }

    public function test_course_description_strips_unsafe_attributes()
    {
        $user = User::factory()->create();
        // Give instructor role
        $instructorRole = Role::where('name', 'instructor')->first();
        $user->roles()->attach($instructorRole);

        $this->actingAs($user);

        // Create dependencies
        $level = \App\Models\CourseLevel::create(['name' => 'Beginner']);
        $category = \App\Models\Category::create(['name' => 'Development', 'slug' => 'development']);

        $payload = [
            'title' => 'Security Course',
            'description' => '<p>Safe</p><a href="javascript:alert(1)" onclick="steal()">Click me</a>',
            'price' => 10,
            'level_id' => $level->id,
            'category_id' => $category->id,
            'language' => 'English',
        ];

        $response = $this->postJson('/api/courses', $payload);

        $response->assertStatus(201); // Created

        $course = Course::first();

        // Expect attributes to be stripped: <a>Click me</a>
        $this->assertStringNotContainsString('javascript:alert', $course->description);
        $this->assertStringNotContainsString('onclick', $course->description);
        $this->assertStringContainsString('<a>Click me</a>', $course->description);
    }
}
