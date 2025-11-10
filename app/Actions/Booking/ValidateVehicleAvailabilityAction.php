<?php

namespace App\Actions\Booking;

use App\Enums\VehicleStatus;
use App\Exceptions\BookingException;
use App\Exceptions\VehicleException;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Carbon\Carbon;

class ValidateVehicleAvailabilityAction
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository
    ) {}

    public function execute(int $vehicleId, string $startDate, string $endDate): Vehicle
    {
        try {
            $vehicle = Vehicle::findOrFail($vehicleId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            throw VehicleException::notFound($vehicleId);
        }

        // Check if vehicle is available for booking
        if (! $vehicle->is_available || $vehicle->status !== VehicleStatus::PUBLISHED) {
            throw VehicleException::notAvailable($vehicleId, 'Vehicle status: '.$vehicle->status->value);
        }

        // Check insurance expiry
        if ($vehicle->insurance_expiry && Carbon::parse($vehicle->insurance_expiry)->isPast()) {
            throw VehicleException::insuranceExpired($vehicleId, $vehicle->insurance_expiry);
        }

        // Check for overlapping bookings using repository with pessimistic locking
        if (! $this->vehicleRepository->checkAvailabilityWithLock($vehicleId, $startDate, $endDate)) {
            throw BookingException::dateConflict($vehicleId, $startDate, $endDate);
        }

        return $vehicle;
    }

    /**
     * Execute availability validation without locking (for read-only checks)
     */
    public function executeReadOnly(int $vehicleId, string $startDate, string $endDate): Vehicle
    {
        try {
            $vehicle = Vehicle::findOrFail($vehicleId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            throw VehicleException::notFound($vehicleId);
        }

        // Check if vehicle is available for booking
        if (! $vehicle->is_available || $vehicle->status !== VehicleStatus::PUBLISHED) {
            throw VehicleException::notAvailable($vehicleId, 'Vehicle status: '.$vehicle->status->value);
        }

        // Check insurance expiry
        if ($vehicle->insurance_expiry && Carbon::parse($vehicle->insurance_expiry)->isPast()) {
            throw VehicleException::insuranceExpired($vehicleId, $vehicle->insurance_expiry);
        }

        // Check for overlapping bookings using repository (without locking)
        if (! $this->vehicleRepository->checkAvailability($vehicleId, $startDate, $endDate)) {
            throw BookingException::dateConflict($vehicleId, $startDate, $endDate);
        }

        return $vehicle;
    }
}
