<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Reservations/Create', []);
    }

    public function reserve(Request $request, int $id): Response
    {
        $vehicle = Vehicle::with(['owner', 'images'])
            ->where('id', $id)
            ->where('status', 'published')
            ->where('is_available', true)
            ->firstOrFail();

        return Inertia::render('CarReservationPage', [
            'car' => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'daily_rate' => $vehicle->daily_rate,
                'transmission' => $vehicle->transmission?->value,
                'fuel_type' => $vehicle->fuel_type?->value,
                'seats' => $vehicle->seats,
                'location' => $vehicle->location,
                'pickup_location' => $vehicle->pickup_location,
                'featured_image' => $this->getFeaturedImage($vehicle),
                'is_available' => $vehicle->is_available,
                'status' => $vehicle->status?->value,
            ],
            'booking_params' => [
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ],
        ]);
    }

    private function getFeaturedImage(Vehicle $vehicle): ?string
    {
        // Check if vehicle has a featured_image set
        if ($vehicle->featured_image) {
            return Storage::url($vehicle->featured_image);
        }

        // Fall back to primary image from images relationship
        $primaryImage = $vehicle->images->where('is_primary', true)->first();
        if ($primaryImage) {
            return Storage::url($primaryImage->image_path);
        }

        // Fall back to first image
        $firstImage = $vehicle->images->first();
        if ($firstImage) {
            return Storage::url($firstImage->image_path);
        }

        return null;
    }
}
