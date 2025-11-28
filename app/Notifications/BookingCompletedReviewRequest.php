<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCompletedReviewRequest extends Notification
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
        return FilamentNotification::make()
            ->title('How was your experience?')
            ->body("Your booking for {$this->booking->vehicle->make} {$this->booking->vehicle->model} has been completed. We'd love to hear your feedback!")
            ->icon('heroicon-o-star')
            ->iconColor('info')
            ->actions([
                ActionsAction::make('review')
                    ->label('Write Review')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button()
                    ->color('primary'),
            ])
            ->getDatabaseMessage();
    }
}
