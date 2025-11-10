<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renter_id')->constrained('car_rental_users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('car_rental_vehicles')->onDelete('cascade');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->decimal('total_amount', 8, 2);
            $table->enum('status', BookingStatus::values())->default(BookingStatus::PENDING->value);
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->text('special_requests')->nullable();
            $table->decimal('deposit_amount', 8, 2)->default(0);
            $table->decimal('commission_amount', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['renter_id', 'status']);
            $table->index(['vehicle_id', 'start_date', 'end_date']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_bookings');
    }
};
