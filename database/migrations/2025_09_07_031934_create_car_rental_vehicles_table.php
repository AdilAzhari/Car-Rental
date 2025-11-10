<?php

use App\Enums\VehicleFuelType;
use App\Enums\VehicleStatus;
use App\Enums\VehicleTransmission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('car_rental_users')->onDelete('cascade');
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('plate_number')->unique();
            $table->string('vin')->unique();
            $table->enum('fuel_type', VehicleFuelType::values());
            $table->enum('transmission', VehicleTransmission::values());
            $table->integer('seats');
            $table->decimal('daily_rate', 8, 2);
            $table->text('description')->nullable();
            $table->enum('status', VehicleStatus::values())->default(VehicleStatus::PENDING->value);
            $table->boolean('is_available')->default(true);
            $table->string('location');
            $table->integer('mileage')->default(0);
            $table->date('insurance_expiry');
            $table->timestamps();
            $table->softDeletes();

            $table->index('plate_number');
            $table->index(['owner_id', 'status']);
            $table->index(['make', 'model']);
            $table->index('daily_rate');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_vehicles');
    }
};
