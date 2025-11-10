<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Models\UserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserFavoritesController extends Controller
{
    /**
     * Display user's favorite cars
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = UserFavorite::where('user_id', $request->user()->id)
            ->with(['vehicle.owner', 'vehicle.images', 'vehicle.reviews'])
            ->get();

        $vehicles = $favorites->map(fn ($favorite) => $favorite->vehicle);

        return CarResource::collection($vehicles);
    }

    /**
     * Add car to favorites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:car_rental_vehicles,id',
        ]);

        $favorite = UserFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'vehicle_id' => $request->vehicle_id,
        ]);

        return response()->json([
            'message' => $favorite->wasRecentlyCreated ? 'Car added to favorites' : 'Car already in favorites',
            'is_favorite' => true,
        ]);
    }

    /**
     * Check if car is favorited
     */
    public function show(Request $request, string $vehicleId): JsonResponse
    {
        $isFavorite = UserFavorite::where('user_id', $request->user()->id)
            ->where('vehicle_id', $vehicleId)
            ->exists();

        return response()->json([
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Remove car from favorites
     */
    public function destroy(Request $request, string $vehicleId): JsonResponse
    {
        $deleted = UserFavorite::where('user_id', $request->user()->id)
            ->where('vehicle_id', $vehicleId)
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Car removed from favorites' : 'Car not in favorites',
            'is_favorite' => false,
        ]);
    }

    /**
     * Toggle favorite status
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:car_rental_vehicles,id',
        ]);

        $favorite = UserFavorite::where('user_id', $request->user()->id)
            ->where('vehicle_id', $request->vehicle_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
            $message = 'Car removed from favorites';
        } else {
            UserFavorite::create([
                'user_id' => $request->user()->id,
                'vehicle_id' => $request->vehicle_id,
            ]);
            $isFavorite = true;
            $message = 'Car added to favorites';
        }

        return response()->json([
            'message' => $message,
            'is_favorite' => $isFavorite,
        ]);
    }
}
