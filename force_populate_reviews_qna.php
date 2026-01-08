<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Review;
use App\Models\CourseQuestion;
use App\Models\Lecture;
use App\Models\User;

echo "Starting forced population for missing content...\n";

// Get students
$students = User::take(100)->get();
if ($students->isEmpty()) {
    $students = User::factory(10)->create();
}

// 1. Populate Reviews
$coursesNoReviews = Course::doesntHave('reviews')->get();
echo "Found " . $coursesNoReviews->count() . " courses with no reviews.\n";

$bar = new Symfony\Component\Console\Helper\ProgressBar(new Symfony\Component\Console\Output\ConsoleOutput(), $coursesNoReviews->count());
$bar->start();

foreach ($coursesNoReviews as $course) {
    Review::factory(rand(2, 5))->create([
        'course_id' => $course->id,
        'user_id' => $students->random()->id ?? 1
    ]);
    $bar->advance();
}
$bar->finish();
echo "\nReviews populated.\n";

// 2. Populate Q&A
$coursesNoQnA = Course::doesntHave('questions')->get();
echo "Found " . $coursesNoQnA->count() . " courses with no questions.\n";

$bar = new Symfony\Component\Console\Helper\ProgressBar(new Symfony\Component\Console\Output\ConsoleOutput(), $coursesNoQnA->count());
$bar->start();

foreach ($coursesNoQnA as $course) {
    // Need lectures for context sometimes, assume sections exist
    $lectures = Lecture::whereHas('section', fn($q) => $q->where('course_id', $course->id))->limit(5)->get();

    foreach (range(1, rand(2, 4)) as $i) {
        CourseQuestion::factory()->create([
            'course_id' => $course->id,
            'user_id' => $students->random()->id ?? 1,
            'lecture_id' => $lectures->isNotEmpty() && rand(0, 1) ? $lectures->random()->id : null
        ]);
    }
    $bar->advance();
}
$bar->finish();
echo "\nQ&A populated.\nDone!";
