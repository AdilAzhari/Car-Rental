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
        Schema::table('car_rental_users', function (Blueprint $table) {
            $table->boolean('is_new_user')->default(true)->after('is_verified');
            $table->boolean('has_changed_default_password')->default(false)->after('is_new_user');
            $table->timestamp('last_login_at')->nullable()->after('has_changed_default_password');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_users', function (Blueprint $table) {
            $table->dropColumn(['is_new_user', 'has_changed_default_password', 'last_login_at', 'password_changed_at']);
        });
    }
};
