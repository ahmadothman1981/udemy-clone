<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add performance indexes to frequently queried tables.
     */
    public function up(): void
    {
        // Enrollments - frequently queried by user_id and course_id
        Schema::table('enrollments', function (Blueprint $table) {
            // $table->index('user_id');
            // $table->index('course_id');
            // $table->index(['user_id', 'course_id']);
        });

        // Orders - frequently queried by user_id and status
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Instructor Earnings - frequently queried by instructor_id and status
        Schema::table('instructor_earnings', function (Blueprint $table) {
            $table->index('instructor_id');
            $table->index('status');
            $table->index('course_id');
        });

        // Reviews - frequently queried by course_id
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('user_id');
        });

        // User Progress - frequently queried for learning tracking
        Schema::table('user_progress', function (Blueprint $table) {
            $table->index('enrollment_id');
            $table->index('lecture_id');
        });

        // Course Questions - frequently queried by course_id
        Schema::table('course_questions', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['course_id']);
            $table->dropIndex(['user_id', 'course_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('instructor_earnings', function (Blueprint $table) {
            $table->dropIndex(['instructor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['course_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex(['enrollment_id']);
            $table->dropIndex(['lecture_id']);
        });

        Schema::table('course_questions', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
