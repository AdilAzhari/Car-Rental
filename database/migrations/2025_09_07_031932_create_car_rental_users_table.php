<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('role', UserRole::values())->default(UserRole::RENTER->value);
            $table->string('license_number')->nullable();
            $table->string('id_document_path')->nullable();
            $table->string('license_document_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', UserStatus::values())->default(UserStatus::PENDING->value);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('role');
            $table->index('status');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_users');
    }
};
