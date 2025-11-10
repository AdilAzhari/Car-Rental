<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * Process payment for a booking
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:car_rental_bookings,id',
            'payment_method' => 'required|in:stripe,stripe_checkout,visa,credit,tng,touch_n_go,cash,bank_transfer',
            'payment_method_id' => 'sometimes|string', // For Stripe payments
        ]);

        try {
            $booking = Booking::findOrFail($request->booking_id);

            // Check if user owns this booking
            if ($booking->renter_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking.',
                ], 403);
            }

            // Check if booking is in valid state for payment
            if (! in_array($booking->status, ['pending', 'pending_payment'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be paid for.',
                ], 400);
            }

            $result = $this->paymentService->processPayment($booking, $request->all());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'user_id' => $request->user()->id,
                'booking_id' => $request->booking_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get payment status for a booking
     */
    public function getPaymentStatus(Request $request, string $bookingId): JsonResponse
    {
        try {
            $booking = Booking::with('payments')->findOrFail($bookingId);

            // Check if user owns this booking
            if ($booking->renter_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking.',
                ], 403);
            }

            $latestPayment = $booking->payments()->latest()->first();

            return response()->json([
                'success' => true,
                'booking_status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'payment' => $latestPayment ? [
                    'id' => $latestPayment->id,
                    'amount' => $latestPayment->amount,
                    'status' => $latestPayment->status,
                    'payment_method' => $latestPayment->payment_method,
                    'created_at' => $latestPayment->created_at,
                ] : null,
            ]);

        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment status.',
            ], 500);
        }
    }

    /**
     * Stripe webhook handler
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle specific webhook events
        if (in_array($event['type'], [
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'payment_intent.canceled',
        ])) {
            $handled = $this->paymentService->handleStripeWebhook($event);

            if (! $handled) {
                Log::warning('Stripe webhook not handled', ['event' => $event]);

                return response()->json(['error' => 'Webhook not handled'], 400);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Create payment intent for Stripe
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:car_rental_bookings,id',
        ]);

        try {
            $booking = Booking::findOrFail($request->booking_id);

            // Check if user owns this booking
            if ($booking->renter_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking.',
                ], 403);
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($booking->total_amount * 100), // Convert to cents
                'currency' => 'MYR',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->renter_id,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'user_id' => $request->user()->id,
                'booking_id' => $request->booking_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent.',
            ], 500);
        }
    }

    /**
     * Create Stripe Checkout session for car booking
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:car_rental_bookings,id',
        ]);

        try {
            $booking = Booking::with('vehicle')->findOrFail($request->booking_id);

            // Check if user owns this booking
            if ($booking->renter_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking.',
                ], 403);
            }

            // Check if booking is in valid state for payment
            if (! in_array($booking->status, ['pending', 'pending_payment'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be paid for.',
                ], 400);
            }

            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower((string) config('app.currency', 'myr')),
                        'product_data' => [
                            'name' => "{$booking->vehicle->make} {$booking->vehicle->model} Rental",
                            'description' => "Car rental from {$booking->start_date} to {$booking->end_date}",
                            'images' => $booking->vehicle->featured_image ? [asset('storage/'.$booking->vehicle->featured_image)] : [],
                        ],
                        'unit_amount' => (int) ($booking->total_amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('booking.payment.success', [
                    'booking' => $booking->id,
                    'session_id' => '{CHECKOUT_SESSION_ID}',
                ]),
                'cancel_url' => route('booking.payment.cancel', $booking->id),
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->renter_id,
                    'vehicle_id' => $booking->vehicle_id,
                ],
                'customer_email' => $request->user()->email,
                'expires_at' => now()->addMinutes(30)->timestamp, // Session expires in 30 minutes
            ]);

            // Create a pending payment record
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => 'stripe',
                'payment_gateway' => 'stripe',
                'gateway_transaction_id' => $session->id,
                'status' => 'pending',
                'gateway_response' => json_encode([
                    'session_id' => $session->id,
                    'session_url' => $session->url,
                ]),
                'processed_at' => now(),
            ]);

            // Update booking status
            $booking->update([
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            Log::info('Stripe Checkout session created', [
                'booking_id' => $booking->id,
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Checkout session creation failed', [
                'user_id' => $request->user()->id,
                'booking_id' => $request->booking_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment session.',
            ], 500);
        }
    }

    /**
     * Confirm cash payment (admin only)
     */
    public function confirmCashPayment(Request $request, string $paymentId): JsonResponse
    {
        $request->validate([
            'confirm' => 'required|boolean',
            'notes' => 'sometimes|string|max:500',
        ]);

        try {
            $payment = Payment::with('booking')->findOrFail($paymentId);

            if ($payment->payment_method !== 'cash') {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a cash payment.',
                ], 400);
            }

            if ($request->confirm) {
                $payment->update([
                    'status' => 'paid',
                    'notes' => $request->notes ?? $payment->notes,
                    'confirmed_at' => now(),
                ]);

                $payment->booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]);

                $message = 'Cash payment confirmed successfully.';
            } else {
                $payment->update([
                    'status' => 'failed',
                    'notes' => $request->notes ?? 'Payment not received',
                ]);

                $payment->booking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);

                $message = 'Cash payment marked as failed.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_status' => $payment->status,
                'booking_status' => $payment->booking->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Cash payment confirmation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status.',
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, string $paymentId): JsonResponse
    {
        $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $payment = Payment::with('booking')->findOrFail($paymentId);

            $refundAmount = $request->amount ?? $payment->amount;

            if ($refundAmount > $payment->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount cannot exceed payment amount.',
                ], 400);
            }

            $result = $this->paymentService->refundPayment($payment, $refundAmount);

            // Update booking status if full refund
            if ($result['success'] && $refundAmount >= $payment->amount) {
                $payment->booking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'refunded',
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed.',
            ], 500);
        }
    }
}
