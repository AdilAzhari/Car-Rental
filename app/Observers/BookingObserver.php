<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingStatusChanged;
use App\Notifications\NewBookingCreated;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        // Notify vehicle owner
        if ($booking->vehicle && $booking->vehicle->owner) {
            $booking->vehicle->owner->notify(new NewBookingCreated($booking));
        }

        // Notify all admins
        $admins = User::where('role', UserRole::ADMIN)->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewBookingCreated($booking));
        }

        // Notify the customer who created the booking
        if ($booking->user) {
            $booking->user->notify(new NewBookingCreated($booking));
        }
    }

    public function updated(Booking $booking): void
    {
        // Check if status changed
        if ($booking->isDirty('status')) {
            $oldStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;

            // Notify vehicle owner
            if ($booking->vehicle && $booking->vehicle->owner) {
                $booking->vehicle->owner->notify(new BookingStatusChanged($booking, $oldStatus, $newStatus));
            }

            // Notify customer
            if ($booking->user) {
                $booking->user->notify(new BookingStatusChanged($booking, $oldStatus, $newStatus));
            }

            // Notify admins
            $admins = User::where('role', UserRole::ADMIN)->get();
            foreach ($admins as $admin) {
                $admin->notify(new BookingStatusChanged($booking, $oldStatus, $newStatus));
            }

            // Special notification for cancellations
            if ($newStatus === 'cancelled') {
                if ($booking->vehicle && $booking->vehicle->owner) {
                    $booking->vehicle->owner->notify(new BookingCancelled($booking));
                }

                if ($booking->user) {
                    $booking->user->notify(new BookingCancelled($booking));
                }
            }
        }
    }
}
