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
            $table->json('parking_violations')->nullable()->after('has_pending_violations');
            $table->timestamp('parking_violations_last_checked')->nullable()->after('parking_violations');
            $table->integer('total_parking_violations_count')->default(0)->after('parking_violations_last_checked');
            $table->decimal('total_parking_fines_amount', 8, 2)->default(0)->after('total_parking_violations_count');
            $table->boolean('has_pending_parking_violations')->default(false)->after('total_parking_fines_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'parking_violations',
                'parking_violations_last_checked',
                'total_parking_violations_count',
                'total_parking_fines_amount',
                'has_pending_parking_violations',
            ]);
        });
    }
};
