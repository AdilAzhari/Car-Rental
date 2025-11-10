<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('car_rental_bookings')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', PaymentMethod::values());
            $table->enum('payment_status', PaymentStatus::values())->default(PaymentStatus::PENDING->value);
            $table->string('transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 8, 2)->default(0);
            $table->timestamps();

            $table->index('booking_id');
            $table->index('payment_status');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_payments');
    }
};
