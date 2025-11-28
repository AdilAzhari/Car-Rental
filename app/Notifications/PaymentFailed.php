<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public float $amount,
        public string $errorMessage
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Payment Failed')
            ->body("Payment of RM" . number_format($this->amount, 2) . " for booking #{$this->booking->id} failed. Error: {$this->errorMessage}")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Booking')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button(),
                ActionsAction::make('retry')
                    ->label('Retry Payment')
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }
}
