<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->enum('payment_status', PaymentStatus::values())->default(PaymentStatus::UNPAID->value);
            $table->enum('payment_method', PaymentMethod::values())->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method']);
        });
    }
};
