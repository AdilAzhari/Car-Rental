<?php

namespace App\Http\Resources;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class CarResource extends JsonResource
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
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'plate_number' => $this->plate_number,
            'fuel_type' => $this->fuel_type?->value,
            'transmission' => $this->transmission?->value,
            'seats' => $this->seats,
            'doors' => $this->doors,
            'daily_rate' => $this->daily_rate,
            'description' => $this->description,
            'location' => $this->location,
            'pickup_location' => $this->pickup_location,
            'mileage' => $this->mileage,
            'category' => $this->category,
            'engine_size' => $this->engine_size,
            'insurance_included' => $this->insurance_included,
            'featured_image' => $this->getFeaturedImageUrl(),
            'gallery_images' => $this->gallery_images,
            'features' => $this->features,
            'policy' => $this->policy,
            'terms_and_conditions' => $this->terms_and_conditions,
            'is_available' => $this->is_available,
            'status' => $this->status?->value,

            // Relationships
            'owner' => $this->whenLoaded('owner', fn (): array => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
                'created_at' => $this->owner->created_at,
            ]),

            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image): array => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'alt_text' => $image->alt_text,
                'is_primary' => $image->is_primary,
            ])),

            'reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->map(fn ($review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
                'renter' => $this->whenLoaded('reviews.renter', [
                    'id' => $review->renter?->id,
                    'name' => $review->renter?->name,
                ]),
            ])),

            // Computed fields
            'average_rating' => $this->whenLoaded('reviews', fn () => $this->reviews->avg('rating') ?: 0),

            'total_reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->count()),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
