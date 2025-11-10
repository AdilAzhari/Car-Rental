<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\Vehicles\VehicleResource as VehiclesVehicleResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\Page;

class PayVehicleFines extends Page
{
    protected static string $resource = VehiclesVehicleResource::class;

    protected string $view = 'filament.resources.vehicle-resource.pages.pay-vehicle-fines';

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
        return 'Pay Vehicle Fines - '.$this->record->plate_number;
    }

    #[\Override]
    public function getSubheading(): ?string
    {
        return $this->record->make.' '.$this->record->model.' ('.$this->record->year.')';
    }

    public function getTotalFines(): float
    {
        $total = 0;

        // Insurance fine
        if ($this->record->insurance_expiry < now() && ! $this->record->insurance_fine_paid) {
            $total += $this->record->insurance_fine_amount;
        }

        // Traffic violations
        if ($this->record->has_pending_violations) {
            $total += $this->record->total_fines_amount;
        }

        // Parking violations
        if ($this->record->has_pending_parking_violations) {
            $total += $this->record->total_parking_fines_amount;
        }

        return $total;
    }

    public function hasInsuranceFine(): bool
    {
        return $this->record->insurance_expiry < now() &&
               $this->record->insurance_fine_amount > 0 &&
               ! $this->record->insurance_fine_paid;
    }

    public function hasTrafficViolations(): bool
    {
        return $this->record->has_pending_violations &&
               $this->record->total_fines_amount > 0;
    }

    public function hasParkingViolations(): bool
    {
        return $this->record->has_pending_parking_violations &&
               $this->record->total_parking_fines_amount > 0;
    }

    public function hasAnyFines(): bool
    {
        return $this->hasInsuranceFine() ||
               $this->hasTrafficViolations() ||
               $this->hasParkingViolations();
    }
}
