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
            // Add back missing columns that are used by the model and forms
            $table->string('category')->nullable()->after('fuel_type');
            $table->integer('doors')->nullable()->after('category');
            $table->integer('seats')->default(5)->after('doors');
            $table->string('color')->nullable()->after('seats');
            $table->decimal('engine_size', 3, 1)->nullable()->after('color');
            $table->integer('mileage')->default(0)->after('engine_size');
            $table->string('location')->nullable()->after('transmission');
            $table->string('pickup_location')->nullable()->after('location');
            $table->boolean('insurance_included')->default(true)->after('pickup_location');
            $table->date('insurance_expiry')->nullable()->after('insurance_included');
            $table->string('featured_image')->nullable()->after('insurance_expiry');
            $table->json('gallery_images')->nullable()->after('featured_image');
            $table->json('documents')->nullable()->after('gallery_images');
            $table->json('features')->nullable()->after('documents');
            $table->text('description')->nullable()->after('features');
            $table->text('terms_and_conditions')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'doors',
                'seats',
                'color',
                'engine_size',
                'mileage',
                'location',
                'pickup_location',
                'insurance_included',
                'insurance_expiry',
                'featured_image',
                'gallery_images',
                'documents',
                'features',
                'description',
                'terms_and_conditions',
            ]);
        });
    }
};
