<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Bundles table
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Bundle courses pivot
        Schema::create('bundle_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['bundle_id', 'course_id']);
        });

        // Gift purchases
        Schema::create('gift_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bundle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('redemption_code')->unique();
            $table->text('message')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_purchases');
        Schema::dropIfExists('bundle_courses');
        Schema::dropIfExists('bundles');
    }
};
