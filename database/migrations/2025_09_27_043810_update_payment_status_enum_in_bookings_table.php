<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing 'paid' values to 'confirmed'
        DB::table('car_rental_bookings')->where('payment_status', 'paid')->update(['payment_status' => 'confirmed']);

        // For SQLite, we need to recreate the table with the new enum values
        // Since SQLite doesn't have native ENUM support, Laravel uses CHECK constraints
        // We'll drop and recreate the check constraint

        // Get current table structure to preserve it
        $columns = Schema::getColumnListing('car_rental_bookings');

        // Use Schema builder to modify the column which handles SQLite compatibility
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            // Drop existing payment_status column
            $table->dropColumn('payment_status');
        });

        // Add the column back with new enum values
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->enum('payment_status', PaymentStatus::values())->default('unpaid');
        });

        // Update any records that might have invalid status values
        DB::table('car_rental_bookings')
            ->whereNotIn('payment_status', PaymentStatus::values())
            ->update(['payment_status' => 'unpaid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert confirmed back to paid
        DB::table('car_rental_bookings')->where('payment_status', 'confirmed')->update(['payment_status' => 'paid']);

        // Recreate the old enum structure
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
        });
    }
};
