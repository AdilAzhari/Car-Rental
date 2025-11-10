<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $lengthAwarePaginator = Booking::with(['vehicle', 'vehicle.images'])
            ->where('renter_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('MyBookingsPage', [
            'bookings' => $lengthAwarePaginator,
        ]);
    }

    public function show(Booking $booking): Response
    {
        // Ensure user can only see their own bookings
        if ($booking->renter_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['vehicle', 'vehicle.images', 'vehicle.owner', 'payments']);

        return Inertia::render('BookingDetailsPage', [
            'booking' => $booking,
        ]);
    }

    public function paymentCheckout(Booking $booking): Response
    {
        // Ensure user can only access payment for their own bookings
        if ($booking->renter_id !== auth()->id()) {
            abort(403);
        }

        // Check if booking is in a valid state for payment
        if (! in_array($booking->status, ['pending', 'pending_payment'])) {
            return redirect()->route('booking.show', $booking)
                ->with('error', 'This booking cannot be paid for.');
        }

        $booking->load(['vehicle', 'vehicle.images']);

        return Inertia::render('PaymentCheckout', [
            'booking' => $booking,
            'stripe_key' => config('services.stripe.key'),
        ]);
    }

    public function paymentReturn(Booking $booking): Response
    {
        // This endpoint handles payment return callbacks from payment gateways
        $booking->load(['vehicle', 'payments']);

        return Inertia::render('PaymentReturn', [
            'booking' => $booking,
            'message' => 'Payment processing completed. Please check your booking status.',
        ]);
    }

    public function paymentSuccess(Booking $booking): Response
    {
        // Ensure user can only access their own booking
        if ($booking->renter_id !== auth()->id()) {
            abort(403);
        }

        $sessionId = request('session_id');

        if ($sessionId) {
            // Verify the Stripe session and update booking status
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    // Update booking status
                    $booking->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                    ]);

                    // Update payment record
                    $payment = $booking->payments()->where('gateway_transaction_id', $sessionId)->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'paid',
                            'gateway_response' => json_encode($session->toArray()),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to verify Stripe session', [
                    'session_id' => $sessionId,
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $booking->load(['vehicle', 'payments']);

        return Inertia::render('PaymentReturn', [
            'booking' => $booking,
            'success' => true,
            'message' => 'Payment successful! Your booking has been confirmed.',
        ]);
    }

    public function paymentCancel(Booking $booking): Response
    {
        // Ensure user can only access their own booking
        if ($booking->renter_id !== auth()->id()) {
            abort(403);
        }

        // Update booking status back to pending
        $booking->update([
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Update payment record as cancelled
        $latestPayment = $booking->payments()->latest()->first();
        if ($latestPayment && $latestPayment->status === 'pending') {
            $latestPayment->update(['status' => 'cancelled']);
        }

        $booking->load(['vehicle', 'payments']);

        return Inertia::render('PaymentReturn', [
            'booking' => $booking,
            'cancelled' => true,
            'message' => 'Payment was cancelled. You can try again by clicking the payment button.',
        ]);
    }
}
