<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
        ];
    }
}
