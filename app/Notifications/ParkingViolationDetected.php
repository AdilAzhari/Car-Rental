<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParkingViolationDetected extends Notification
{
    use Queueable;

    public function __construct(
        public Vehicle $vehicle,
        public int $violationCount,
        public float $totalFines
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Parking Violation Detected')
            ->body("{$this->vehicle->make} {$this->vehicle->model} ({$this->vehicle->plate_number}) has {$this->violationCount} parking violation(s). Total fines: RM" . number_format($this->totalFines, 2))
            ->icon('heroicon-o-exclamation-circle')
            ->iconColor('warning')
            ->actions([
                ActionsAction::make('view')
                    ->label('View Violations')
                    ->url(route('filament.admin.resources.vehicles.edit', ['record' => $this->vehicle->id]))
                    ->button(),
                ActionsAction::make('pay')
                    ->label('Pay Fines')
                    ->url(route('filament.admin.resources.vehicles.edit', ['record' => $this->vehicle->id]))
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }
}
