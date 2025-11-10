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
            // Drop unique index first if it exists
            $table->dropUnique(['vin']);
            $table->dropColumn('vin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->string('vin', 17)->nullable()->unique()->after('plate_number');
        });
    }
};
