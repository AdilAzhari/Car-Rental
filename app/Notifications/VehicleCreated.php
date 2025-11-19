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
        $ownerName = $this->vehicle->owner ? $this->vehicle->owner->name : 'Unknown Owner';

        return [
            'title' => 'New Vehicle Submission',
            'message' => "A new vehicle '{$this->vehicle->make} {$this->vehicle->model}' ({$this->vehicle->year}) has been submitted by {$ownerName} for approval.",
            'vehicle_id' => $this->vehicle->id,
            'vehicle_status' => $this->vehicle->status->value,
            'owner_name' => $ownerName,
            'action_url' => route('filament.admin.resources.vehicles.edit', $this->vehicle),
            'action_label' => 'Review Vehicle',
        ];
    }
}
