<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CourseLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 200);
        return [
            'title' => $title = fake()->sentence(),
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'thumbnail' => "https://loremflickr.com/640/360/business,tech?random=" . fake()->numberBetween(1, 1000),
            'price' => $price,
            'discount_price' => fake()->boolean(60) ? $price * 0.8 : null,
            'level_id' => CourseLevel::inRandomOrder()->first()?->id ?? 1,
            'language' => fake()->randomElement(['English', 'Spanish', 'French']),
            'estimated_hours' => fake()->numberBetween(1, 100),
            'instructor_id' => User::factory(), // This might create too many users if not careful in seeder, but valid for factory definition
            'category_id' => Category::factory(),
            'subcategory_id' => null, // logic to handle in seeder or state
            'rating_avg' => fake()->randomFloat(1, 3, 5),
            'enrollment_count' => fake()->numberBetween(0, 10000),
            'published' => fake()->boolean(80),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
