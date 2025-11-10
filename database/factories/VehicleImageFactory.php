<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleImageFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['economy', 'compact', 'midsize', 'luxury', 'suv', 'sports', 'convertible', 'pickup', 'minivan', 'electric'];
        fake()->randomElement($categories);

        // Only use actual image files that exist
        $availableImages = [
            // Actual files that exist in storage/app/public/vehicles/
            'economy-toyota-corolla.jpg',
            'economy-nissan-altima.jpg',
            'compact-honda-civic.jpg',
            'compact-volkswagen-golf.jpg',
            'midsize-toyota-camry.jpg',
            'midsize-hyundai-sonata.jpg',
            'luxury-bmw-3series.jpg',
            'luxury-mercedes-cclass.jpg',
            'luxury-audi-a4.jpg',
            'luxury-lexus-es.jpg',
            'luxury-jaguar-xf.jpg',
            'suv-toyota-rav4.jpg',
            'suv-honda-crv.jpg',
            'suv-ford-explorer.jpg',
            'sports-porsche-911.jpg',
            'sports-bmw-m4.jpg',
            'sports-ferrari.jpg',
            'sports-lamborghini.jpg',
            'convertible-bmw-z4.jpg',
            'convertible-mercedes-slc.jpg',
            'pickup-ford-f150.jpg',
            'minivan-honda-odyssey.jpg',
        ];

        $imagePath = 'vehicles/'.fake()->randomElement($availableImages);

        return [
            'vehicle_id' => Vehicle::factory(),
            'image_path' => $imagePath,
            'alt_text' => fake()->randomElement([
                'Front view of the vehicle',
                'Side view of the car',
                'Interior dashboard view',
                'Rear view of the automobile',
                'Engine compartment view',
                'Driver seat interior',
                'Wheel and tire close-up',
                'Trunk/cargo space',
                'Steering wheel and controls',
                'Passenger compartment',
                'Vehicle profile shot',
                'Headlight detail',
                'Exterior styling detail',
                'Dashboard instruments',
                'Seat upholstery detail',
            ]),
            'is_primary' => fake()->boolean(15), // 15% chance of being primary
        ];
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
