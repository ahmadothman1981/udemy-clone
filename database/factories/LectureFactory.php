<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lecture>
 */
class LectureFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['video', 'article', 'quiz', 'resource']);
        return [
            'section_id' => Section::factory(),
            'title' => fake()->sentence(),
            'type' => $type,
            'content' => $type !== 'video' ? fake()->paragraphs(3, true) : null,
            'video_url' => $type === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
            'duration_minutes' => $type === 'video' ? fake()->numberBetween(5, 60) : null,
            'order' => fake()->numberBetween(1, 10),
            'preview' => fake()->boolean(20),
            'free_preview' => fake()->boolean(10),
        ];
    }
}
