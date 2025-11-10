<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmationForRenter;
use App\Notifications\NewBookingForOwner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendBookingNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BookingCreated $bookingCreated): void
    {
        $booking = $bookingCreated->booking;

        try {
            // Send confirmation to renter
            $booking->renter->notify(new BookingConfirmationForRenter($booking));

            Log::info('Booking confirmation sent to renter', [
                'booking_id' => $booking->id,
                'renter_id' => $booking->renter_id,
                'renter_email' => $booking->renter->email,
            ]);

            // Send notification to vehicle owner
            $booking->vehicle->owner->notify(new NewBookingForOwner($booking));

            Log::info('New booking notification sent to owner', [
                'booking_id' => $booking->id,
                'owner_id' => $booking->vehicle->owner_id,
                'owner_email' => $booking->vehicle->owner->email,
            ]);

            // Send notification to admin for cash payments
            if ($booking->payment_method === 'cash') {
                $adminUsers = \App\Models\User::role('admin')->get();
                Notification::send($adminUsers, new \App\Notifications\CashBookingPendingApproval($booking));

                Log::info('Cash booking notification sent to admins', [
                    'booking_id' => $booking->id,
                    'admin_count' => $adminUsers->count(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send booking notifications', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(BookingCreated $bookingCreated, \Throwable $throwable): void
    {
        Log::error('Booking notification job failed', [
            'booking_id' => $bookingCreated->booking->id,
            'error' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
        ]);
    }
}
