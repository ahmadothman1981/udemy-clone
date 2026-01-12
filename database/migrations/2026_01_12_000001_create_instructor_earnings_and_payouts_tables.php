<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instructor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->decimal('gross_amount', 10, 2); // Full course price
            $table->decimal('platform_fee', 10, 2)->default(0); // Platform cut (e.g. 30%)
            $table->decimal('net_amount', 10, 2); // What instructor receives
            $table->enum('status', ['pending', 'available', 'paid'])->default('pending');
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['paypal', 'bank_transfer', 'stripe'])->default('paypal');
            $table->enum('status', ['requested', 'processing', 'completed', 'failed'])->default('requested');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('instructor_earnings');
    }
};
