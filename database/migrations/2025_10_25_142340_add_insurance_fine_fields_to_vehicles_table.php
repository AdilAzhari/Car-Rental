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
            $table->decimal('insurance_fine_amount', 8, 2)->default(0)->after('insurance_expiry');
            $table->boolean('insurance_fine_paid')->default(false)->after('insurance_fine_amount');
            $table->timestamp('insurance_fine_paid_at')->nullable()->after('insurance_fine_paid');
            $table->string('insurance_fine_payment_method')->nullable()->after('insurance_fine_paid_at');
            $table->string('insurance_fine_transaction_id')->nullable()->after('insurance_fine_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'insurance_fine_amount',
                'insurance_fine_paid',
                'insurance_fine_paid_at',
                'insurance_fine_payment_method',
                'insurance_fine_transaction_id',
            ]);
        });
    }
};
