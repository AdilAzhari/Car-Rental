<?php

namespace App\Actions\Vehicle;

use App\Http\Resources\CarResource;
use App\Repositories\VehicleRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchVehiclesAction
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository
    ) {}

    public function execute(Request $request): AnonymousResourceCollection
    {
        \App\DTOs\VehicleSearchDTO::fromRequest($request);
        $lengthAwarePaginator = $this->vehicleRepository->searchWithFilters($request);

        return CarResource::collection($lengthAwarePaginator);
    }

    public function executeWithDTO(\App\DTOs\VehicleSearchDTO $vehicleSearchDTO): AnonymousResourceCollection
    {
        // Convert DTO back to request-like structure for repository compatibility
        $mockRequest = new Request($vehicleSearchDTO->toArray());
        $lengthAwarePaginator = $this->vehicleRepository->searchWithFilters($mockRequest);

        return CarResource::collection($lengthAwarePaginator);
    }
}
