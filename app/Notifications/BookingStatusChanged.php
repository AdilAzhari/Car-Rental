<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $statusColors = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'active' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return [
            'title' => 'Booking Status Changed',
            'body' => "Booking #{$this->booking->id} status changed from {$this->oldStatus} to {$this->newStatus}",
            'icon' => 'heroicon-o-arrow-path',
            'icon_color' => $statusColors[$this->newStatus] ?? 'gray',
            'booking_id' => $this->booking->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'actions' => [
                [
                    'label' => 'View Booking',
                    'url' => route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]),
                ],
            ],
        ];
    }
}
