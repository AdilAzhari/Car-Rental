<?php

namespace App\Http\Controllers\Api;

use App\Actions\Vehicle\GetVehicleDetailsAction;
use App\Actions\Vehicle\SearchVehiclesAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarController extends Controller
{
    public function __construct(
        private readonly SearchVehiclesAction $searchVehiclesAction,
        private readonly GetVehicleDetailsAction $getVehicleDetailsAction
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return $this->searchVehiclesAction->execute($request);
    }

    public function show(int $id): CarResource
    {
        return $this->getVehicleDetailsAction->execute($id);
    }
}
