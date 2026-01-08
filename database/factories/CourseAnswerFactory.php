<?php

namespace Database\Factories;

use App\Models\CourseQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseAnswer>
 */
class CourseAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_question_id' => CourseQuestion::factory(),
            'user_id' => User::factory(),
            'answer' => fake()->paragraph(),
            'best_answer' => fake()->boolean(10),
        ];
    }
}
