<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public BookingStatus $oldStatus,
        public BookingStatus $newStatus
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

        return FilamentNotification::make()
            ->title('Booking Status Changed')
            ->body("Booking #{$this->booking->id} status changed from {$this->oldStatus->value} to {$this->newStatus->value}")
            ->icon('heroicon-o-arrow-path')
            ->iconColor($statusColors[$this->newStatus->value] ?? 'gray')
            ->actions([
                Action::make('view')
                    ->label('View Booking')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
