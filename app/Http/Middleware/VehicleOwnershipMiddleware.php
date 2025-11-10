<?php

namespace App\Http\Middleware;

use App\Exceptions\VehicleException;
use App\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VehicleOwnershipMiddleware
{
    /**
     * @throws VehicleException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $vehicleId = $request->route('vehicle') ?? $request->route('id') ?? $request->vehicle_id ?? $request->car_id;

        if (! $vehicleId) {
            throw VehicleException::unauthorized(0, auth()->id());
        }

        $vehicle = Vehicle::find($vehicleId);

        if (! $vehicle) {
            throw VehicleException::notFound($vehicleId);
        }

        $user = auth()->user();

        // Check if user is the vehicle owner or admin
        $isAuthorized = $vehicle->owner_id === $user->id || $user->hasRole('admin');

        if (! $isAuthorized) {
            throw VehicleException::unauthorized($vehicleId, $user->id);
        }

        // Add vehicle to request for easy access in controllers
        $request->merge(['_vehicle' => $vehicle]);

        return $next($request);
    }
}
