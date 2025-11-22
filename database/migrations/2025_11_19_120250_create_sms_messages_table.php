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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('car_rental_vehicles')->onDelete('cascade');
            $table->string('plate_number')->nullable()->index();
            $table->string('message_sid')->unique(); // Twilio message ID
            $table->string('from_number'); // Sender's phone number
            $table->string('to_number'); // Recipient's phone number
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->text('message_body'); // SMS content
            $table->string('message_type')->default('jpj_response'); // Type: jpj_response, notification, etc.
            $table->enum('status', ['received', 'processed', 'failed', 'sent', 'delivered', 'pending'])->default('received');
            $table->json('parsed_data')->nullable(); // Structured data parsed from SMS
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['vehicle_id', 'created_at']);
            $table->index(['plate_number', 'created_at']);
            $table->index(['message_type', 'created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
