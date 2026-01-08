<?php

namespace Database\Factories;

use App\Models\Lecture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lecture_id' => Lecture::factory(),
            'title' => fake()->sentence() . ' Quiz',
            'pass_percentage' => fake()->randomElement([50, 70, 80]),
            'time_limit' => fake()->randomElement([10, 15, 30, null]),
        ];
    }
}
