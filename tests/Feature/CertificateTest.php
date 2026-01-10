<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Lecture;
use App\Models\UserProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $instructor = User::factory()->create();
        $this->course = Course::factory()->create([
            'instructor_id' => $instructor->id,
        ]);

        // Create course content
        $section = Section::create([
            'course_id' => $this->course->id,
            'title' => 'Section 1',
            'position' => 1,
        ]);

        Lecture::create([
            'section_id' => $section->id,
            'title' => 'Lecture 1',
            'position' => 1,
        ]);

        Lecture::create([
            'section_id' => $section->id,
            'title' => 'Lecture 2',
            'position' => 2,
        ]);
    }

    public function test_cannot_generate_certificate_without_enrollment()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/courses/{$this->course->id}/certificate");

        $response->assertStatus(403);
    }

    public function test_cannot_generate_certificate_without_completion()
    {
        Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/courses/{$this->course->id}/certificate");

        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'progress', 'required']);
    }

    public function test_can_generate_certificate_on_completion()
    {
        Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        // Mark all lectures as complete
        $lectures = $this->course->sections->flatMap->lectures;
        foreach ($lectures as $lecture) {
            UserProgress::create([
                'user_id' => $this->user->id,
                'lecture_id' => $lecture->id,
                'completed' => true,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson("/api/courses/{$this->course->id}/certificate");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'certificate' => ['id', 'certificate_number', 'issued_at'],
            ]);
    }

    public function test_can_verify_certificate()
    {
        $certificate = Certificate::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'certificate_number' => 'CERT-TEST1234-2026',
            'issued_at' => now(),
        ]);

        $response = $this->getJson("/api/verify/{$certificate->certificate_number}");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'certificate' => [
                    'certificate_number' => 'CERT-TEST1234-2026',
                    'holder_name' => $this->user->name,
                ],
            ]);
    }

    public function test_invalid_certificate_returns_not_found()
    {
        $response = $this->getJson('/api/verify/INVALID-CERT-123');

        $response->assertStatus(404)
            ->assertJson(['valid' => false]);
    }
}
