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
        // Add admin control fields to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('admin_hidden')->default(false)->after('status');
            $table->text('hidden_reason')->nullable()->after('admin_hidden');
            $table->boolean('qna_enabled')->default(true)->after('hidden_reason');
        });

        // Create platform settings table
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general'); // general, payment, security, localization
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default platform settings
        \DB::table('platform_settings')->insert([
            [
                'key' => 'platform_commission',
                'value' => '30',
                'type' => 'integer',
                'group' => 'payment',
                'description' => 'Platform commission percentage on course sales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payout_minimum',
                'value' => '50',
                'type' => 'integer',
                'group' => 'payment',
                'description' => 'Minimum balance required for instructor payout',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Minimum password length required',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'session_timeout',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Session timeout in minutes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enabled_languages',
                'value' => '["en","ar"]',
                'type' => 'json',
                'group' => 'localization',
                'description' => 'List of enabled languages',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_language',
                'value' => 'en',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Default platform language',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['admin_hidden', 'hidden_reason', 'qna_enabled']);
        });

        Schema::dropIfExists('platform_settings');
    }
};
