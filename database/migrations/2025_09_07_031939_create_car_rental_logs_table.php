<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('car_rental_vehicles')->onDelete('cascade');
            $table->string('action');
            $table->text('description');
            $table->foreignId('user_id')->nullable()->constrained('car_rental_users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('action');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_logs');
    }
};
