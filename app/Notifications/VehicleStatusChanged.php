<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Vehicle $vehicle,
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
            'available' => 'success',
            'rented' => 'info',
            'maintenance' => 'warning',
            'unavailable' => 'danger',
            'published' => 'success',
        ];

        return FilamentNotification::make()
            ->title('Vehicle Status Changed')
            ->body("{$this->vehicle->make} {$this->vehicle->model} ({$this->vehicle->plate_number}) status changed from {$this->oldStatus} to {$this->newStatus}")
            ->icon('heroicon-o-truck')
            ->iconColor($statusColors[$this->newStatus] ?? 'gray')
            ->actions([
                Action::make('view')
                    ->label('View Vehicle')
                    ->url(route('filament.admin.resources.vehicles.edit', ['record' => $this->vehicle->id]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
