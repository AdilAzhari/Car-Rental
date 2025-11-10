<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleCreated extends Notification
{
    use Queueable;

    public function __construct(public Vehicle $vehicle) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "New vehicle '{$this->vehicle->make} {$this->vehicle->model}' has been created.",
            'vehicle_id' => $this->vehicle->id,
            'action_url' => route('filament.admin.resources.vehicles.edit', $this->vehicle),
        ];
    }
}
