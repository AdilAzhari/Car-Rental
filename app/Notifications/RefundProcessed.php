<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefundProcessed extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public float $refundAmount
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Refund Processed')
            ->body("A refund of RM" . number_format($this->refundAmount, 2) . " for booking #{$this->booking->id} has been processed. The amount will be credited to your account within 5-7 business days.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Booking')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
