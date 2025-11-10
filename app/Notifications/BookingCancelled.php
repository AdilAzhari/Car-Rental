<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public ?string $cancellationReason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $body = "Booking #{$this->booking->id} for {$this->booking->vehicle->make} {$this->booking->vehicle->model} has been cancelled";

        if ($this->cancellationReason) {
            $body .= ". Reason: {$this->cancellationReason}";
        }

        return [
            'title' => 'Booking Cancelled',
            'body' => $body,
            'icon' => 'heroicon-o-x-circle',
            'icon_color' => 'danger',
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'cancellation_reason' => $this->cancellationReason,
            'actions' => [
                [
                    'label' => 'View Booking',
                    'url' => route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]),
                ],
            ],
        ];
    }
}
