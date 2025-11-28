<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueBooking extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public int $hoursOverdue
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Overdue Booking - Vehicle Not Returned')
            ->body("Booking #{$this->booking->id} for {$this->booking->vehicle->make} {$this->booking->vehicle->model} ({$this->booking->vehicle->plate_number}) is overdue by {$this->hoursOverdue} hour(s). Renter: {$this->booking->renter->name}")
            ->icon('heroicon-o-clock')
            ->iconColor('danger')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Booking')
                    ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                    ->button(),
                ActionsAction::make('contact')
                    ->label('Contact Renter')
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }
}
