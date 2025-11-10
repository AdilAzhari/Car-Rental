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
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            // Add calculated booking fields
            $table->integer('days')->nullable()->after('end_date')->comment('Number of rental days');
            $table->decimal('daily_rate', 10, 2)->nullable()->after('days')->comment('Daily rental rate from vehicle');
            $table->decimal('subtotal', 10, 2)->nullable()->after('daily_rate')->comment('Days × Daily Rate');
            $table->decimal('insurance_fee', 10, 2)->nullable()->after('subtotal')->comment('Insurance fee (10% of subtotal)');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('insurance_fee')->comment('Tax amount (15% of subtotal)');

            // Make total_amount nullable and update its position
            $table->decimal('total_amount', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'days',
                'daily_rate',
                'subtotal',
                'insurance_fee',
                'tax_amount',
            ]);

            // Restore total_amount to not nullable
            $table->decimal('total_amount', 8, 2)->nullable(false)->change();
        });
    }
};
