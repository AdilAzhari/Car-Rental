<?php

namespace App\Notifications;

use App\Models\Vehicle;
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
        ];

        return [
            'title' => 'Vehicle Status Changed',
            'body' => "{$this->vehicle->make} {$this->vehicle->model} ({$this->vehicle->plate_number}) status changed from {$this->oldStatus} to {$this->newStatus}",
            'icon' => 'heroicon-o-truck',
            'icon_color' => $statusColors[$this->newStatus] ?? 'gray',
            'vehicle_id' => $this->vehicle->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'actions' => [
                [
                    'label' => 'View Vehicle',
                    'url' => route('filament.admin.resources.vehicles.view', ['record' => $this->vehicle->id]),
                ],
            ],
        ];
    }
}
