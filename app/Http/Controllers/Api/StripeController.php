<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function __construct()
    {
        // Set Stripe API key
        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Create a payment intent for a booking
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:car_rental_bookings,id',
        ]);

        try {
            $booking = Booking::with(['vehicle', 'renter'])->findOrFail($request->booking_id);

            // Check if user owns this booking
            if ($booking->renter_id !== auth()->id() && auth()->user()->role->value !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking.',
                ], 403);
            }

            // Check if booking is already paid
            $existingPayment = Payment::where('booking_id', $booking->id)
                ->whereIn('payment_status', ['completed', 'succeeded', 'paid'])
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking has already been paid.',
                ], 400);
            }

            // Calculate amount in cents (Stripe uses smallest currency unit)
            $amountInCents = (int) ($booking->total_amount * 100);

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => strtolower((string) config('app.currency', 'myr')),
                'description' => "Booking #{$booking->id} - {$booking->vehicle->make} {$booking->vehicle->model}",
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => auth()->id(),
                    'vehicle_id' => $booking->vehicle_id,
                    'user_email' => auth()->user()->email,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create pending payment record
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'transaction_id' => $paymentIntent->id,
                'payment_date' => now(),
                'payment_details' => json_encode([
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'status' => $paymentIntent->status,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $booking->total_amount,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent: '.$e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment Intent Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }

    /**
     * Handle successful payment confirmation
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'booking_id' => 'required|exists:car_rental_bookings,id',
        ]);

        try {
            DB::beginTransaction();

            // Retrieve the payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            $booking = Booking::findOrFail($request->booking_id);

            // Verify the payment belongs to this booking
            if ($paymentIntent->metadata->booking_id != $booking->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment does not match this booking.',
                ], 400);
            }

            // Update or create payment record
            $payment = Payment::where('transaction_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->update([
                    'payment_status' => $paymentIntent->status === 'succeeded' ? 'completed' : $paymentIntent->status,
                    'payment_details' => json_encode([
                        'payment_intent_id' => $paymentIntent->id,
                        'status' => $paymentIntent->status,
                        'amount_received' => $paymentIntent->amount_received / 100,
                        'payment_method' => $paymentIntent->payment_method,
                        'confirmed_at' => now(),
                    ]),
                ]);
            } else {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $paymentIntent->amount_received / 100,
                    'payment_method' => 'stripe',
                    'payment_status' => $paymentIntent->status === 'succeeded' ? 'completed' : $paymentIntent->status,
                    'transaction_id' => $paymentIntent->id,
                    'payment_date' => now(),
                    'payment_details' => json_encode([
                        'payment_intent_id' => $paymentIntent->id,
                        'status' => $paymentIntent->status,
                        'amount_received' => $paymentIntent->amount_received / 100,
                        'payment_method' => $paymentIntent->payment_method,
                    ]),
                ]);
            }

            // Update booking status if payment succeeded
            if ($paymentIntent->status === 'succeeded') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully.',
                'payment' => $payment,
                'booking' => $booking,
            ]);

        } catch (ApiErrorException $e) {
            DB::rollBack();
            Log::error('Stripe Confirm Payment Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment: '.$e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm Payment Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while confirming payment.',
            ], 500);
        }
    }

    /**
     * Process alternative payment methods (TNG, Cash)
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:car_rental_bookings,id',
            'payment_method' => 'required|in:tng,cash,bank_transfer',
        ]);

        try {
            DB::beginTransaction();

            $booking = Booking::with(['vehicle', 'renter'])->findOrFail($request->booking_id);

            // Check authorization
            if ($booking->renter_id !== auth()->id() && auth()->user()->role->value !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking.',
                ], 403);
            }

            // Check if already paid
            $existingPayment = Payment::where('booking_id', $booking->id)
                ->whereIn('payment_status', ['completed', 'succeeded', 'paid'])
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking has already been paid.',
                ], 400);
            }

            $paymentStatus = $request->payment_method === 'cash' ? 'pending' : 'processing';
            $transactionId = 'TXN-'.strtoupper(uniqid());

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'transaction_id' => $transactionId,
                'payment_date' => now(),
                'payment_details' => json_encode([
                    'method' => $request->payment_method,
                    'initiated_at' => now(),
                ]),
            ]);

            // Update booking
            if ($request->payment_method === 'cash') {
                $booking->update([
                    'payment_status' => 'pending',
                ]);
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => $this->getPaymentMethodMessage($request->payment_method),
                'payment' => $payment,
                'booking_id' => $booking->id,
            ];

            // Add payment URL for TNG if needed
            if ($request->payment_method === 'tng') {
                $response['payment_url'] = route('payment.tng.redirect', ['booking' => $booking->id]);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Payment Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment.',
            ], 500);
        }
    }

    /**
     * Handle payment return/callback
     */
    public function paymentReturn(Request $request, Booking $booking): JsonResponse
    {
        try {
            $paymentIntentId = $request->get('payment_intent');
            $status = $request->get('status');

            if ($paymentIntentId) {
                // Retrieve payment intent from Stripe
                $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

                // Update payment record
                $payment = Payment::where('transaction_id', $paymentIntentId)->first();

                if ($payment) {
                    $payment->update([
                        'payment_status' => $paymentIntent->status === 'succeeded' ? 'completed' : $paymentIntent->status,
                        'payment_details' => json_encode([
                            'payment_intent_id' => $paymentIntent->id,
                            'status' => $paymentIntent->status,
                            'completed_at' => now(),
                        ]),
                    ]);

                    if ($paymentIntent->status === 'succeeded') {
                        $booking->update([
                            'status' => 'confirmed',
                            'payment_status' => 'paid',
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'booking' => $booking->load(['vehicle', 'renter', 'payments']),
                'payment_status' => $status ?? 'unknown',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment Return Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment return.',
            ], 500);
        }
    }

    /**
     * Get Stripe publishable key
     */
    public function getPublishableKey(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'publishable_key' => config('cashier.key'),
        ]);
    }

    /**
     * Get payment method message
     */
    private function getPaymentMethodMessage(string $method): string
    {
        return match ($method) {
            'tng' => 'Redirecting to Touch \'n Go payment page...',
            'cash' => 'Payment will be collected on vehicle pickup. Your booking is confirmed.',
            'bank_transfer' => 'Please complete the bank transfer. Details sent to your email.',
            default => 'Payment initiated successfully.',
        };
    }

    /**
     * Webhook handler for Stripe events
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('cashier.webhook.secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentSuccess($paymentIntent);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentFailure($paymentIntent);
                    break;

                default:
                    Log::info('Unhandled Stripe event: '.$event->type);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle successful payment from webhook
     */
    private function handlePaymentSuccess($paymentIntent): void
    {
        $payment = Payment::where('transaction_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'payment_status' => 'completed',
                'payment_details' => json_encode([
                    'payment_intent_id' => $paymentIntent->id,
                    'status' => 'succeeded',
                    'amount_received' => $paymentIntent->amount_received / 100,
                    'webhook_received_at' => now(),
                ]),
            ]);

            $payment->booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            Log::info("Payment succeeded for booking #{$payment->booking_id}");
        }
    }

    /**
     * Handle failed payment from webhook
     */
    private function handlePaymentFailure($paymentIntent): void
    {
        $payment = Payment::where('transaction_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'payment_status' => 'failed',
                'payment_details' => json_encode([
                    'payment_intent_id' => $paymentIntent->id,
                    'status' => 'failed',
                    'failure_message' => $paymentIntent->last_payment_error?->message,
                    'webhook_received_at' => now(),
                ]),
            ]);

            Log::warning("Payment failed for booking #{$payment->booking_id}");
        }
    }
}
