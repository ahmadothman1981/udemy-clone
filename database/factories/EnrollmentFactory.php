<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'amount_paid' => fake()->randomFloat(2, 10, 200),
            'enrolled_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'completed_at' => fake()->boolean(20) ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
