<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New Booking Created',
            'body' => "New booking #{$this->booking->id} for {$this->booking->vehicle->make} {$this->booking->vehicle->model}",
            'icon' => 'heroicon-o-calendar',
            'icon_color' => 'success',
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'actions' => [
                [
                    'label' => 'View Booking',
                    'url' => route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]),
                ],
            ],
        ];
    }
}
