<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        $options = [
            'A' => fake()->word(),
            'B' => fake()->word(),
            'C' => fake()->word(),
            'D' => fake()->word(),
        ];
        return [
            'quiz_id' => Quiz::factory(),
            'question_text' => fake()->sentence() . '?',
            'options' => $options,
            'correct_answer' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'points' => fake()->numberBetween(1, 10),
        ];
    }
}
