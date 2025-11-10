<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->json('traffic_violations')->nullable()->after('policy');
            $table->timestamp('violations_last_checked')->nullable()->after('traffic_violations');
            $table->integer('total_violations_count')->default(0)->after('violations_last_checked');
            $table->decimal('total_fines_amount', 10, 2)->default(0)->after('total_violations_count');
            $table->boolean('has_pending_violations')->default(false)->after('total_fines_amount');

            $table->index(['has_pending_violations']);
            $table->index(['violations_last_checked']);
        });
    }

    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->dropIndex(['has_pending_violations']);
            $table->dropIndex(['violations_last_checked']);

            $table->dropColumn([
                'traffic_violations',
                'violations_last_checked',
                'total_violations_count',
                'total_fines_amount',
                'has_pending_violations',
            ]);
        });
    }
};
