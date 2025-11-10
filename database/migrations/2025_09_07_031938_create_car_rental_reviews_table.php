<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('car_rental_bookings')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('car_rental_vehicles')->onDelete('cascade');
            $table->foreignId('renter_id')->constrained('car_rental_users')->onDelete('cascade');
            $table->integer('rating')->unsigned()->between(1, 5);
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('renter_id');
            $table->index('rating');
            $table->unique('booking_id'); // Ensure one review per booking
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_reviews');
    }
};
