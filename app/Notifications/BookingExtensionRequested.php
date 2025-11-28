<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingExtensionRequested extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $requestedEndDate,
        public int $additionalDays
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Booking Extension Requested')
            ->body("Renter {$this->booking->renter->name} requests to extend booking #{$this->booking->id} for {$this->booking->vehicle->make} {$this->booking->vehicle->model} by {$this->additionalDays} day(s) until {$this->requestedEndDate}")
            ->icon('heroicon-o-calendar-days')
            ->iconColor('info')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Booking')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button(),
                ActionsAction::make('approve')
                    ->label('Approve')
                    ->button()
                    ->color('success'),
            ])
            ->getDatabaseMessage();
    }
}
