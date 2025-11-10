<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingConfirmedSms extends Notification implements ShouldQueue
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
        $startDate = $this->booking->start_date->format('M d, Y');
        $endDate = $this->booking->end_date->format('M d, Y');

        $message = "Dear {$notifiable->name}, your booking #{$this->booking->id} for {$vehicleName} from {$startDate} to {$endDate} has been confirmed. Total: \${$this->booking->total_amount}. Thank you for choosing our service!";

        return [
            'to' => $notifiable->phone,
            'message' => $message,
        ];
    }
}
