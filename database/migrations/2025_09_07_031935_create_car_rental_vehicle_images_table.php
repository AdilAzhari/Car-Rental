<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('car_rental_vehicles')->onDelete('cascade');
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_vehicle_images');
    }
};
