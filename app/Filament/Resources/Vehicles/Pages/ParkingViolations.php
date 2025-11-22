<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\Vehicles\VehicleResource as VehiclesVehicleResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\Page;

class ParkingViolations extends Page
{
    protected static string $resource = VehiclesVehicleResource::class;

    protected string $view = 'filament.resources.vehicle-resource.pages.parking-violations';

    public Vehicle $record;

    public function mount(Vehicle $vehicle): void
    {
        $this->record = $vehicle;
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canView($this->record), 403);
    }

    #[\Override]
    public function getTitle(): string
    {
        return '';
        //        return 'Parking & Municipal Violations - '.$this->record->plate_number;
    }

    #[\Override]
    public function getSubheading(): ?string
    {
        return $this->record->make.' '.$this->record->model.' ('.$this->record->year.')';
    }

    public function getViolations(): array
    {
        return $this->record->parking_violations ?? [];
    }
}
