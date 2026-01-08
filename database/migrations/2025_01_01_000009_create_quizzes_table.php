<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->onDelete('cascade'); // ERD says 'fk lecture_id'. Wait, is a quiz PART OF a lecture or IS it a lecture type? ERD has LECTURES ||--o| QUIZZES : "contains". This implies a lecture CAN have a quiz.
            $table->string('title');
            $table->integer('pass_percentage')->default(50);
            $table->integer('time_limit')->nullable(); // minutes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
