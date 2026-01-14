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
        Schema::table('users', function (Blueprint $table) {
            // Replace is_banned with status enum
            $table->enum('status', ['active', 'deactivated', 'banned'])->default('active')->after('email');

            // Add localization fields
            $table->string('language', 10)->default('en')->after('bio');
            $table->string('country', 100)->nullable()->after('language');

            // Instructor restrictions (JSON for flexibility)
            $table->json('instructor_restrictions')->nullable()->after('instructor_verification_status');
        });

        // Migrate existing banned users
        \DB::table('users')->where('is_banned', true)->update(['status' => 'banned']);

        // Drop old is_banned column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('email');
        });

        // Migrate back
        \DB::table('users')->where('status', 'banned')->update(['is_banned' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'language', 'country', 'instructor_restrictions']);
        });
    }
};
