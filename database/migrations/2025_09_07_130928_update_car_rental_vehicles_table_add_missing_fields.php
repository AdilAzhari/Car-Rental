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
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            // Add missing columns that are in the form but not in the original migration
            $table->string('oil_type')->nullable()->after('daily_rate');
            $table->date('last_oil_change')->nullable()->after('oil_type');
            $table->longText('policy')->nullable()->after('last_oil_change');

            // Drop the index first before dropping the column
            $table->dropIndex('car_rental_vehicles_is_available_index');

            // Remove or make nullable fields that were in original migration but not in our model
            $table->dropColumn(['color', 'seats', 'description', 'location', 'mileage', 'insurance_expiry', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            // Restore original columns
            $table->string('color')->after('year');
            $table->integer('seats')->after('transmission');
            $table->text('description')->nullable()->after('daily_rate');
            $table->boolean('is_available')->default(true)->after('status');
            $table->string('location')->after('is_available');
            $table->integer('mileage')->default(0)->after('location');
            $table->date('insurance_expiry')->after('mileage');

            // Restore the index
            $table->index('is_available');

            // Remove added columns
            $table->dropColumn(['oil_type', 'last_oil_change', 'policy']);

            // Make VIN required again
            $table->string('vin')->nullable(false)->change();
        });
    }
};
