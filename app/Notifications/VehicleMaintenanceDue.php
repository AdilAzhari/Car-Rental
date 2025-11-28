<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleMaintenanceDue extends Notification
{
    use Queueable;

    public function __construct(
        public Vehicle $vehicle,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Vehicle Maintenance Due')
            ->body("{$this->vehicle->make} {$this->vehicle->model} ({$this->vehicle->plate_number}) requires maintenance. Reason: {$this->reason}")
            ->icon('heroicon-o-wrench-screwdriver')
            ->iconColor('warning')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Vehicle')
                    ->url(route('filament.admin.resources.vehicles.edit', ['record' => $this->vehicle->id]))
                    ->button(),
                ActionsAction::make('schedule')
                    ->label('Schedule Maintenance')
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }
}
