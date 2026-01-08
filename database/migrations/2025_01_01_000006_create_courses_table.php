<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discount_price', 8, 2)->nullable();

            // Foreign Keys
            $table->foreignId('level_id')->constrained('course_levels'); // ERD says 'int level', mapped to course_levels.
            $table->string('language');
            $table->integer('estimated_hours')->nullable();

            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('cascade');

            $table->float('rating_avg')->default(0);
            $table->integer('enrollment_count')->default(0);
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // Indexes for performance as requested
            $table->index('title');
            $table->index('price');
            $table->index('rating_avg');
            $table->index('published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
