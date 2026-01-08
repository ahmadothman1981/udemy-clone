<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saved_payment_methods', function (Blueprint $table) {
            $table->string('type')->default('card')->after('user_id');
            $table->string('email')->nullable()->after('type');
            $table->string('brand')->nullable()->change();
            $table->string('last4')->nullable()->change();
            $table->integer('exp_month')->nullable()->change();
            $table->integer('exp_year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saved_payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'email']);
            $table->string('brand')->nullable(false)->change();
            $table->string('last4')->nullable(false)->change();
            $table->integer('exp_month')->nullable(false)->change();
            $table->integer('exp_year')->nullable(false)->change();
        });
    }
};
