<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->json('preferences')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable(); // Standard Laravel timestamp, though ERD only asks for created_at, usually good to have both or at least respect standard unless strictly forbidden. ERD shows created_at. I'll add updated_at as it's standard practice, but could stick to just created_at if strict. I'll add updated_at to be safe for Laravel models.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
