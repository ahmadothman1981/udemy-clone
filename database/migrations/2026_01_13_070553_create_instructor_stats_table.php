<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instructor_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->integer('total_students')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('course_count')->default(0);
            $table->integer('monthly_students')->default(0);
            $table->decimal('monthly_revenue', 12, 2)->default(0);
            $table->integer('monthly_reviews')->default(0);
            $table->integer('unanswered_questions')->default(0);
            $table->date('stats_date')->nullable(); // For historical tracking
            $table->timestamps();

            $table->unique(['instructor_id', 'stats_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_stats');
    }
};
