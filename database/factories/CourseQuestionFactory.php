<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseQuestion>
 */
class CourseQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'lecture_id' => fake()->boolean(50) ? Lecture::factory() : null, // Nullable
            'user_id' => User::factory(),
            'question' => fake()->sentence() . '?',
            'asked_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
