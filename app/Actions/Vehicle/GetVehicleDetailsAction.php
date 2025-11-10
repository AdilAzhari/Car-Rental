<?php

namespace App\Actions\Vehicle;

use App\Http\Resources\CarResource;
use App\Repositories\VehicleRepository;

class GetVehicleDetailsAction
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository
    ) {}

    public function execute(int $vehicleId): CarResource
    {
        $vehicle = $this->vehicleRepository->findWithDetails($vehicleId);

        return new CarResource($vehicle);
    }
}
