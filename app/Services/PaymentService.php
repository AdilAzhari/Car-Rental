<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentService
{
    public function __construct()
    {
        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Process payment based on payment method
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        $paymentMethod = $paymentData['payment_method'];

        return match ($paymentMethod) {
            'stripe', 'visa', 'credit' => $this->processStripePayment($booking, $paymentData),
            'stripe_checkout' => $this->processStripeCheckout($booking),
            'tng', 'touch_n_go' => $this->processTouchNGoPayment($booking),
            'cash' => $this->processCashPayment($booking),
            'bank_transfer' => $this->processBankTransferPayment($booking),
            default => throw new Exception("Unsupported payment method: {$paymentMethod}")
        };
    }

    /**
     * Process Stripe payment (Credit Card, Visa, etc.)
     */
    private function processStripePayment(Booking $booking, array $paymentData): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => CurrencyHelper::toCents($booking->total_amount),
                'currency' => strtolower((string) config('app.currency', 'myr')),
                'payment_method' => $paymentData['payment_method_id'] ?? null,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('booking.payment.return', $booking->id),
                'metadata' => [
                    'booking_id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'user_id' => $booking->renter_id,
                ],
                'description' => "Car rental booking #{$booking->id}",
            ]);

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => $paymentData['payment_method'],
                'payment_gateway' => 'stripe',
                'gateway_transaction_id' => $paymentIntent->id,
                'status' => $this->mapStripeStatus($paymentIntent->status),
                'gateway_response' => json_encode($paymentIntent->toArray()),
                'processed_at' => now(),
            ]);

            // Update booking status based on payment status
            if ($paymentIntent->status === 'succeeded') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]);

                return [
                    'success' => true,
                    'status' => 'succeeded',
                    'payment_id' => $payment->id,
                    'booking_status' => 'confirmed',
                    'message' => 'Payment successful! Your booking is confirmed.',
                ];
            } elseif ($paymentIntent->status === 'requires_action') {
                return [
                    'success' => false,
                    'status' => 'requires_action',
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_id' => $payment->id,
                    'message' => 'Additional authentication required.',
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $paymentIntent->status,
                    'payment_id' => $payment->id,
                    'message' => 'Payment failed. Please try again.',
                ];
            }

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'stripe_error' => $e->getStripeCode(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Payment processing failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Process Stripe Checkout payment (redirect to hosted checkout)
     */
    private function processStripeCheckout(Booking $booking): array
    {
        // For Stripe Checkout, we just need to set the booking to pending_payment status
        // The actual payment processing happens via the checkout session
        $booking->update([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        Log::info('Booking prepared for Stripe Checkout', [
            'booking_id' => $booking->id,
            'payment_method' => 'stripe_checkout',
        ]);

        return [
            'success' => true,
            'status' => 'pending_checkout',
            'message' => 'Booking created. Redirecting to payment...',
            'redirect_to_checkout' => true,
        ];
    }

    /**
     * Process Touch 'n Go payment (Malaysian e-wallet)
     */
    private function processTouchNGoPayment(Booking $booking): array
    {
        try {
            // For demo purposes - in real implementation, you'd integrate with TNG API
            // This would typically involve creating a payment request and redirecting to TNG

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => 'tng',
                'payment_gateway' => 'touch_n_go',
                'gateway_transaction_id' => 'TNG_'.uniqid(),
                'status' => 'pending',
                'gateway_response' => json_encode([
                    'payment_url' => 'https://tng.example.com/pay/'.uniqid(),
                    'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
                ]),
                'processed_at' => now(),
            ]);

            $booking->update([
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            return [
                'success' => true,
                'status' => 'pending',
                'payment_id' => $payment->id,
                'booking_status' => 'pending_payment',
                'payment_url' => 'https://tng.example.com/pay/'.uniqid(),
                'message' => 'Please complete payment through Touch \'n Go.',
            ];

        } catch (Exception $e) {
            Log::error('Touch n Go payment failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Touch \'n Go payment processing failed.',
            ];
        }
    }

    /**
     * Process cash payment (pay on pickup)
     */
    private function processCashPayment(Booking $booking): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'payment_method' => 'cash',
            'payment_gateway' => 'cash',
            'status' => 'pending',
            'notes' => 'Payment to be made in cash upon vehicle pickup',
            'processed_at' => now(),
        ]);

        $booking->update([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        return [
            'success' => true,
            'status' => 'pending',
            'payment_id' => $payment->id,
            'booking_status' => 'pending_payment',
            'message' => 'Booking created! Please pay in cash when picking up the vehicle.',
        ];
    }

    /**
     * Process bank transfer payment
     */
    private function processBankTransferPayment(Booking $booking): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'payment_method' => 'bank_transfer',
            'payment_gateway' => 'bank_transfer',
            'status' => 'pending',
            'notes' => 'Please transfer to account: 1234567890 (ABC Bank)',
            'processed_at' => now(),
        ]);

        $booking->update([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        return [
            'success' => true,
            'status' => 'pending',
            'payment_id' => $payment->id,
            'booking_status' => 'pending_payment',
            'bank_details' => [
                'account_number' => '1234567890',
                'bank_name' => 'ABC Bank',
                'account_name' => 'RentCar Pro Sdn Bhd',
                'reference' => 'BOOKING-'.$booking->id,
            ],
            'message' => 'Please complete bank transfer using the provided details.',
        ];
    }

    /**
     * Map Stripe payment status to our internal status
     */
    private function mapStripeStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded' => 'paid',
            'pending' => 'unpaid',
            'requires_action', 'requires_confirmation' => 'unpaid',
            'canceled', 'failed' => 'unpaid',
            default => 'unpaid'
        };
    }

    /**
     * Handle payment webhook from Stripe
     */
    public function handleStripeWebhook(array $webhookData): bool
    {
        try {
            $paymentIntent = $webhookData['data']['object'];
            $bookingId = $paymentIntent['metadata']['booking_id'] ?? null;

            if (! $bookingId) {
                Log::warning('Stripe webhook missing booking_id', $webhookData);

                return false;
            }

            $booking = Booking::find($bookingId);
            $payment = Payment::where('booking_id', $bookingId)
                ->where('gateway_transaction_id', $paymentIntent['id'])
                ->first();

            if (! $booking || ! $payment) {
                Log::warning('Booking or payment not found for webhook', [
                    'booking_id' => $bookingId,
                    'payment_intent' => $paymentIntent['id'],
                ]);

                return false;
            }

            // Update payment status
            $payment->update([
                'status' => $this->mapStripeStatus($paymentIntent['status']),
                'gateway_response' => json_encode($paymentIntent),
            ]);

            // Update booking status
            if ($paymentIntent['status'] === 'succeeded') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]);
            } elseif (in_array($paymentIntent['status'], ['canceled', 'failed'])) {
                $booking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'unpaid',
                ]);
            }

            return true;

        } catch (Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);

            return false;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, ?float $amount = null): array
    {
        if ($payment->payment_gateway !== 'stripe') {
            return [
                'success' => false,
                'message' => 'Refunds only supported for Stripe payments currently.',
            ];
        }

        try {
            $refundAmount = $amount ? CurrencyHelper::toCents($amount) : null;

            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->gateway_transaction_id,
                'amount' => $refundAmount,
                'metadata' => [
                    'booking_id' => $payment->booking_id,
                    'payment_id' => $payment->id,
                ],
            ]);

            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $amount ?? $payment->amount,
                'refund_reference' => $refund->id,
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'Refund processed successfully.',
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Refund processing failed: '.$e->getMessage(),
            ];
        }
    }
}
