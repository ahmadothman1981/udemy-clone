<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SavedPaymentMethod;
use App\Models\Message;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\Role;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\Section;
use App\Models\Lecture;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = ['student', 'instructor', 'admin'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }
        $studentRole = Role::where('name', 'student')->first();
        $instructorRole = Role::where('name', 'instructor')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // 2. Levels
        $levels = ['beginner', 'intermediate', 'advanced'];
        foreach ($levels as $level) {
            CourseLevel::create(['name' => $level]);
        }

        // 3. Categories (10)
        $categories = Category::factory(10)->create();

        // 4. Instructors (50)
        $instructors = User::factory(50)->create();
        foreach ($instructors as $instructor) {
            // Manually attach role for custom table
            DB::table('user_roles')->insert([
                'user_id' => $instructor->id,
                'role_id' => $instructorRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Students (Use Factory count to have a base pool, e.g., 200)
        $students = User::factory(200)->create();
        foreach ($students as $student) {
            DB::table('user_roles')->insert([
                'user_id' => $student->id,
                'role_id' => $studentRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Admin User
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@udemyclone.com',
        ]);
        DB::table('user_roles')->insert([
            'user_id' => $admin->id,
            'role_id' => $adminRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Courses (500)
        // We will create courses and assign them to random instructors and categories
        $courses = Course::factory(500)->make()->each(function ($course) use ($instructors, $categories) {
            $course->instructor_id = $instructors->random()->id;
            $course->category_id = $categories->random()->id;
            $course->save();
        });

        // Reload courses to get IDs
        $courses = Course::all();

        // 7. Lectures (5000 Total)
        // Avg 10 lectures per course. 
        // We will iterate courses and add sections/lectures to reach ~5000.
        // Actually, let's just create Sections for all courses, then populate lectures.

        $sectionsPerCourse = 2;
        $lecturesPerSection = 5;
        // 500 courses * 2 sections * 5 lectures = 5000 lectures. Perfect.

        foreach ($courses as $course) {
            $sections = Section::factory($sectionsPerCourse)->create(['course_id' => $course->id]);
            foreach ($sections as $section) {
                $lectures = Lecture::factory($lecturesPerSection)->create(['section_id' => $section->id]);

                foreach ($lectures as $lecture) {
                    // if quiz type, create quiz and questions
                    if ($lecture->type === 'quiz') {
                        $quiz = Quiz::factory()->create(['lecture_id' => $lecture->id]);
                        Question::factory(3)->create(['quiz_id' => $quiz->id]);
                    }
                }
            }
        }

        // 8. Enrollments (2000) + Progress
        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->get(); // Actually we need relay on our manual pivot or just usage User::all() minus instr/admin.
        // Or just use the $students collection we created earlier.

        // Since we didn't setup the relationship 'roles' in User model completely with the query builder for our custom table in a way that allows whereHas easily without defined Eloquent relationship (we defined it in Model but didn't verify it works with custom table structure fully yet). 
        // Let's just use the $students collection variable (which are the 200 newly created ones).

        // Create 2000 enrollments distributed.
        // Loop 2000 times or just assign 10 courses to each student.
        $enrollments = [];
        foreach ($students as $student) {
            // Enroll each student in 10 random courses
            $randomCourses = $courses->random(10);
            foreach ($randomCourses as $course) {
                $enrollment = Enrollment::factory()->create([
                    'user_id' => $student->id,
                    'course_id' => $course->id
                ]);

                // Progress: Mark some lectures complete
                // Get some lectures from the course
                $courseLectures = $course->lectures()->take(3)->get(); // Get first 3
                foreach ($courseLectures as $lecture) {
                    DB::table('user_progress')->insert([
                        'enrollment_id' => $enrollment->id,
                        'lecture_id' => $lecture->id,
                        'completed' => true,
                        'completed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 9. Reviews (1000)
        Review::factory(1000)->make()->each(function ($review) use ($students, $courses) {
            $review->user_id = $students->random()->id;
            $review->course_id = $courses->random()->id;
            $review->save();
        });

        // 10. Q&A (Course Questions & Answers)
        // 500 questions
        $questions = CourseQuestion::factory(500)->make()->each(function ($q) use ($students, $courses) {
            $q->user_id = $students->random()->id;
            $q->course_id = $courses->random()->id;
            $q->save();
        });

        // Answers for some questions
        foreach ($questions as $question) {
            if (rand(0, 1)) {
                CourseAnswer::factory()->create([
                    'course_question_id' => $question->id,
                    'user_id' => $instructors->random()->id, // Instructors answering
                ]);
            }
        }
        // 11. Orders & OrderItems (500 orders)

        // Create orders for random students
        Order::factory(500)->make()->each(function ($order) use ($students, $courses) {
            $order->user_id = $students->random()->id;
            $order->save();

            // Add 1-3 items per order
            $orderCourses = $courses->random(rand(1, 3));
            foreach ($orderCourses as $course) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'price' => $course->price, // Assuming price exists on course
                ]);
            }
        });
    
        // 12. Saved Payment Methods (Random for students)
        foreach ($students as $student) {
            // 50% chance to have a payment method
            if (rand(0, 1)) {
                // Determine type: card or paypal
                $type = rand(0, 1) ? 'card' : 'paypal';
                SavedPaymentMethod::factory()->create([
                    'user_id' => $student->id,
                    'type' => $type,
                    // Factory handles the rest based on defaults, but we should override based on type
                    'email' => $type === 'paypal' ? 'paypal-' . $student->id . '@example.com' : null,
                    'brand' => $type === 'card' ? 'Visa' : null,
                    'last4' => $type === 'card' ? '4242' : null,
                ]);
            }
        }

        // 13. Messages
        // Create conversations between random students and instructors
        foreach ($students->take(50) as $student) { // First 50 students
            $instructor = $instructors->random();
            
            // Student sends message
            Message::create([
                'sender_id' => $student->id,
                'receiver_id' => $instructor->id,
                'content' => 'Hello instructor, I have a question about the course.',
                'created_at' => now()->subDays(rand(1, 10)),
            ]);

            // Instructor replies
            Message::create([
                'sender_id' => $instructor->id,
                'receiver_id' => $student->id,
                'content' => 'Sure, what would you like to know?',
                'created_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        // 14. Notifications
        // Notify some users about "Course Updated" or similar
        // Since we don't have a specific Notification class instance ready for seeding without importing it,
        // we can insert directly into the notifications table for simplicity, OR use a factory if we made one.
        // Let's insert raw DB data for generic testing or assuming we simply notify.
        
        foreach ($students->take(50) as $student) {
             DB::table('notifications')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\CourseUpdated', // Mock type
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $student->id,
                'data' => json_encode(['message' => 'A course you enrolled in has been updated!', 'action_url' => '/']),
                'read_at' => rand(0, 1) ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
