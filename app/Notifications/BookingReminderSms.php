<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingReminderSms extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): array
    {
        $vehicleName = $this->booking->vehicle->make.' '.$this->booking->vehicle->model;
        $startDate = $this->booking->start_date->format('M d, Y \a\t g:i A');
        $pickupLocation = $this->booking->pickup_location ?? 'our location';

        $message = "Reminder: Your booking #{$this->booking->id} for {$vehicleName} starts tomorrow ({$startDate}) at {$pickupLocation}. Please bring your ID and driver's license. Safe travels!";

        return [
            'to' => $notifiable->phone,
            'message' => $message,
        ];
    }
}
