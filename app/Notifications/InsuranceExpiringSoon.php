<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InsuranceExpiringSoon extends Notification
{
    use Queueable;

    public function __construct(
        public Vehicle $vehicle,
        public int $daysUntilExpiry
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Insurance Expiring Soon',
            'body' => "Insurance for {$this->vehicle->make} {$this->vehicle->model} ({$this->vehicle->plate_number}) expires in {$this->daysUntilExpiry} days",
            'icon' => 'heroicon-o-shield-exclamation',
            'icon_color' => $this->daysUntilExpiry <= 7 ? 'danger' : 'warning',
            'vehicle_id' => $this->vehicle->id,
            'days_until_expiry' => $this->daysUntilExpiry,
            'expiry_date' => $this->vehicle->insurance_expiry?->format('Y-m-d'),
            'actions' => [
                [
                    'label' => 'View Vehicle',
                    'url' => route('filament.admin.resources.vehicles.view', ['record' => $this->vehicle->id]),
                ],
                [
                    'label' => 'Update Insurance',
                    'url' => route('filament.admin.resources.vehicles.edit', ['record' => $this->vehicle->id]),
                ],
            ],
        ];
    }
}
