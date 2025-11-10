<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_amount' => $this->total_amount,
            'deposit_amount' => $this->deposit_amount,
            'commission_amount' => $this->commission_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'pickup_location' => $this->pickup_location,
            'dropoff_location' => $this->dropoff_location,
            'special_requests' => $this->special_requests,

            // Relationships
            'vehicle' => $this->whenLoaded('vehicle', fn (): array => [
                'id' => $this->vehicle->id,
                'make' => $this->vehicle->make,
                'model' => $this->vehicle->model,
                'year' => $this->vehicle->year,
                'color' => $this->vehicle->color,
                'plate_number' => $this->vehicle->plate_number,
                'daily_rate' => $this->vehicle->daily_rate,
                'featured_image' => $this->vehicle->featured_image ? Storage::url($this->vehicle->featured_image) : null,
                'transmission' => $this->vehicle->transmission?->value,
                'fuel_type' => $this->vehicle->fuel_type?->value,
                'seats' => $this->vehicle->seats,

                // Vehicle owner info
                'owner' => $this->whenLoaded('vehicle.owner', [
                    'id' => $this->vehicle->owner?->id,
                    'name' => $this->vehicle->owner?->name,
                    'email' => $this->vehicle->owner?->email,
                ]),

                // Vehicle images
                'images' => $this->whenLoaded('vehicle.images', fn () => $this->vehicle->images->map(fn ($image): array => [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'alt_text' => $image->alt_text,
                ])),
            ]),

            'renter' => $this->whenLoaded('renter', fn (): array => [
                'id' => $this->renter->id,
                'name' => $this->renter->name,
                'email' => $this->renter->email,
            ]),

            // Computed fields
            'duration_days' => $this->start_date && $this->end_date
                ? $this->start_date->diffInDays($this->end_date) + 1
                : null,

            'daily_rate' => $this->whenLoaded('vehicle', fn () => $this->vehicle->daily_rate),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
