<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Lecture Resources (downloadable files)
        Schema::create('lecture_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamps();
        });

        // Student Notes
        Schema::create('user_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('lecture_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('content');
            $table->unsignedInteger('video_timestamp')->nullable(); // seconds into video
            $table->timestamps();

            $table->index(['user_id', 'course_id']);
        });

        // Course Preview Video
        Schema::table('courses', function (Blueprint $table) {
            $table->string('preview_video_url')->nullable()->after('thumbnail');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('preview_video_url');
        });
        Schema::dropIfExists('user_notes');
        Schema::dropIfExists('lecture_resources');
    }
};
