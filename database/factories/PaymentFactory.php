<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $paymentMethods = PaymentMethod::values();
        $paymentMethod = fake()->randomElement($paymentMethods);
        $paymentStatus = fake()->randomElement(PaymentStatus::values());
        $gateways = ['stripe', 'paypal'];

        $isDigitalPayment = $paymentMethod !== 'cash';
        $transactionId = $isDigitalPayment ? 'TXN-'.fake()->bothify('#?#?#?#?#?#?') : null;

        $failureReasons = [
            'Insufficient funds',
            'Card declined',
            'Expired card',
            'Invalid card details',
            'Bank processing error',
            'Network timeout',
            'Security check failed',
        ];

        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->numberBetween(30, 750),
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_id' => $transactionId,
            'processed_at' => $paymentStatus === 'confirmed' ? fake()->dateTimeThisMonth() : null,
            'gateway_response' => $isDigitalPayment ? [
                'gateway' => fake()->randomElement($gateways),
                'response_code' => $paymentStatus === 'confirmed' ? '200' : fake()->randomElement(['400', '401', '402', '403', '500']),
                'message' => $paymentStatus === 'confirmed' ? 'Payment processed successfully' : fake()->randomElement($failureReasons),
                'reference_id' => fake()->bothify('REF-#?#?#?#?'),
            ] : null,
            'refunded_at' => null,
            'refund_amount' => 0,
        ];
    }

    public function confirmed(): static
    {
        return $this->state([
            'payment_status' => 'confirmed',
            'processed_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'payment_status' => 'failed',
            'processed_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => 'refunded',
            'refunded_at' => fake()->dateTimeBetween($attributes['processed_at'] ?? now()->subDays(30), 'now'),
            'refund_amount' => fake()->boolean(80) ? $attributes['amount'] : $attributes['amount'] * 0.5,
        ]);
    }
}
