<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $vehicleData = [
            // Economy Cars
            ['make' => 'Toyota', 'model' => 'Corolla', 'category' => 'economy', 'image' => 'economy-toyota-corolla.jpg'],
            ['make' => 'Nissan', 'model' => 'Altima', 'category' => 'economy', 'image' => 'economy-nissan-altima.jpg'],
            ['make' => 'Hyundai', 'model' => 'Elantra', 'category' => 'economy', 'image' => 'economy-hyundai-elantra.jpg'],
            ['make' => 'Kia', 'model' => 'Forte', 'category' => 'economy', 'image' => 'economy-kia-forte.jpg'],
            ['make' => 'Chevrolet', 'model' => 'Malibu', 'category' => 'economy', 'image' => 'economy-chevrolet-malibu.jpg'],
            ['make' => 'Ford', 'model' => 'Focus', 'category' => 'economy', 'image' => 'economy-ford-focus.jpg'],

            // Compact Cars
            ['make' => 'Honda', 'model' => 'Civic', 'category' => 'compact', 'image' => 'compact-honda-civic.jpg'],
            ['make' => 'Volkswagen', 'model' => 'Golf', 'category' => 'compact', 'image' => 'compact-volkswagen-golf.jpg'],
            ['make' => 'Mazda', 'model' => '3', 'category' => 'compact', 'image' => 'compact-mazda-3.jpg'],
            ['make' => 'Subaru', 'model' => 'Impreza', 'category' => 'compact', 'image' => 'compact-subaru-impreza.jpg'],
            ['make' => 'Mini', 'model' => 'Cooper', 'category' => 'compact', 'image' => 'compact-mini-cooper.jpg'],
            ['make' => 'Toyota', 'model' => 'Yaris', 'category' => 'compact', 'image' => 'compact-toyota-yaris.jpg'],

            // Midsize Cars
            ['make' => 'Toyota', 'model' => 'Camry', 'category' => 'midsize', 'image' => 'midsize-toyota-camry.jpg'],
            ['make' => 'Honda', 'model' => 'Accord', 'category' => 'midsize', 'image' => 'midsize-honda-accord.jpg'],
            ['make' => 'Hyundai', 'model' => 'Sonata', 'category' => 'midsize', 'image' => 'midsize-hyundai-sonata.jpg'],
            ['make' => 'Nissan', 'model' => 'Maxima', 'category' => 'midsize', 'image' => 'midsize-nissan-maxima.jpg'],
            ['make' => 'Mazda', 'model' => '6', 'category' => 'midsize', 'image' => 'midsize-mazda-6.jpg'],
            ['make' => 'Kia', 'model' => 'Optima', 'category' => 'midsize', 'image' => 'midsize-kia-optima.jpg'],

            // Luxury Cars
            ['make' => 'BMW', 'model' => '3 Series', 'category' => 'luxury', 'image' => 'luxury-bmw-3series.jpg'],
            ['make' => 'Mercedes-Benz', 'model' => 'C-Class', 'category' => 'luxury', 'image' => 'luxury-mercedes-cclass.jpg'],
            ['make' => 'Audi', 'model' => 'A4', 'category' => 'luxury', 'image' => 'luxury-audi-a4.jpg'],
            ['make' => 'Lexus', 'model' => 'ES', 'category' => 'luxury', 'image' => 'luxury-lexus-es.jpg'],
            ['make' => 'Jaguar', 'model' => 'XF', 'category' => 'luxury', 'image' => 'luxury-jaguar-xf.jpg'],
            ['make' => 'Cadillac', 'model' => 'CT5', 'category' => 'luxury', 'image' => 'luxury-cadillac-ct5.jpg'],
            ['make' => 'Genesis', 'model' => 'G80', 'category' => 'luxury', 'image' => 'luxury-genesis-g80.jpg'],
            ['make' => 'Volvo', 'model' => 'S90', 'category' => 'luxury', 'image' => 'luxury-volvo-s90.jpg'],
            ['make' => 'BMW', 'model' => '5 Series', 'category' => 'luxury', 'image' => 'luxury-bmw-5series.jpg'],
            ['make' => 'Mercedes-Benz', 'model' => 'E-Class', 'category' => 'luxury', 'image' => 'luxury-mercedes-eclass.jpg'],

            // SUVs
            ['make' => 'Toyota', 'model' => 'RAV4', 'category' => 'suv', 'image' => 'suv-toyota-rav4.jpg'],
            ['make' => 'Honda', 'model' => 'CR-V', 'category' => 'suv', 'image' => 'suv-honda-crv.jpg'],
            ['make' => 'Ford', 'model' => 'Explorer', 'category' => 'suv', 'image' => 'suv-ford-explorer.jpg'],
            ['make' => 'Chevrolet', 'model' => 'Tahoe', 'category' => 'suv', 'image' => 'suv-chevrolet-tahoe.jpg'],
            ['make' => 'Jeep', 'model' => 'Grand Cherokee', 'category' => 'suv', 'image' => 'suv-jeep-grand-cherokee.jpg'],
            ['make' => 'BMW', 'model' => 'X3', 'category' => 'suv', 'image' => 'suv-bmw-x3.jpg'],
            ['make' => 'Mercedes-Benz', 'model' => 'GLE', 'category' => 'suv', 'image' => 'suv-mercedes-gle.jpg'],
            ['make' => 'Audi', 'model' => 'Q5', 'category' => 'suv', 'image' => 'suv-audi-q5.jpg'],
            ['make' => 'Lexus', 'model' => 'RX', 'category' => 'suv', 'image' => 'suv-lexus-rx.jpg'],
            ['make' => 'Mazda', 'model' => 'CX-5', 'category' => 'suv', 'image' => 'suv-mazda-cx5.jpg'],

            // Sports Cars
            ['make' => 'Porsche', 'model' => '911', 'category' => 'sports', 'image' => 'sports-porsche-911.jpg'],
            ['make' => 'BMW', 'model' => 'M4', 'category' => 'sports', 'image' => 'sports-bmw-m4.jpg'],
            ['make' => 'Ferrari', 'model' => '488', 'category' => 'sports', 'image' => 'sports-ferrari-488.jpg'],
            ['make' => 'Lamborghini', 'model' => 'Huracan', 'category' => 'sports', 'image' => 'sports-lamborghini-huracan.jpg'],
            ['make' => 'Ford', 'model' => 'Mustang', 'category' => 'sports', 'image' => 'sports-ford-mustang.jpg'],
            ['make' => 'Chevrolet', 'model' => 'Corvette', 'category' => 'sports', 'image' => 'sports-chevrolet-corvette.jpg'],
            ['make' => 'Audi', 'model' => 'R8', 'category' => 'sports', 'image' => 'sports-audi-r8.jpg'],
            ['make' => 'McLaren', 'model' => '720S', 'category' => 'sports', 'image' => 'sports-mclaren-720s.jpg'],
            ['make' => 'Nissan', 'model' => 'GT-R', 'category' => 'sports', 'image' => 'sports-nissan-gtr.jpg'],

            // Convertibles
            ['make' => 'BMW', 'model' => 'Z4', 'category' => 'convertible', 'image' => 'convertible-bmw-z4.jpg'],
            ['make' => 'Mercedes-Benz', 'model' => 'SLC', 'category' => 'convertible', 'image' => 'convertible-mercedes-slc.jpg'],
            ['make' => 'Audi', 'model' => 'TT', 'category' => 'convertible', 'image' => 'convertible-audi-tt.jpg'],
            ['make' => 'Porsche', 'model' => 'Boxster', 'category' => 'convertible', 'image' => 'convertible-porsche-boxster.jpg'],
            ['make' => 'Mazda', 'model' => 'MX-5', 'category' => 'convertible', 'image' => 'convertible-mazda-mx5.jpg'],
            ['make' => 'Ford', 'model' => 'Mustang Convertible', 'category' => 'convertible', 'image' => 'convertible-ford-mustang.jpg'],

            // Pickup Trucks
            ['make' => 'Ford', 'model' => 'F-150', 'category' => 'pickup', 'image' => 'pickup-ford-f150.jpg'],
            ['make' => 'Chevrolet', 'model' => 'Silverado', 'category' => 'pickup', 'image' => 'pickup-chevrolet-silverado.jpg'],
            ['make' => 'Toyota', 'model' => 'Tacoma', 'category' => 'pickup', 'image' => 'pickup-toyota-tacoma.jpg'],
            ['make' => 'RAM', 'model' => '1500', 'category' => 'pickup', 'image' => 'pickup-ram-1500.jpg'],
            ['make' => 'Nissan', 'model' => 'Titan', 'category' => 'pickup', 'image' => 'pickup-nissan-titan.jpg'],
            ['make' => 'GMC', 'model' => 'Sierra', 'category' => 'pickup', 'image' => 'pickup-gmc-sierra.jpg'],

            // Minivans
            ['make' => 'Honda', 'model' => 'Odyssey', 'category' => 'minivan', 'image' => 'minivan-honda-odyssey.jpg'],
            ['make' => 'Toyota', 'model' => 'Sienna', 'category' => 'minivan', 'image' => 'minivan-toyota-sienna.jpg'],
            ['make' => 'Chrysler', 'model' => 'Pacifica', 'category' => 'minivan', 'image' => 'minivan-chrysler-pacifica.jpg'],

            // Electric Vehicles
            ['make' => 'Tesla', 'model' => 'Model 3', 'category' => 'electric', 'image' => 'electric-tesla-model3.jpg'],
            ['make' => 'Tesla', 'model' => 'Model S', 'category' => 'electric', 'image' => 'electric-tesla-models.jpg'],
            ['make' => 'Tesla', 'model' => 'Model Y', 'category' => 'electric', 'image' => 'electric-tesla-modely.jpg'],
            ['make' => 'Nissan', 'model' => 'Leaf', 'category' => 'electric', 'image' => 'electric-nissan-leaf.jpg'],
            ['make' => 'BMW', 'model' => 'i4', 'category' => 'electric', 'image' => 'electric-bmw-i4.jpg'],
            ['make' => 'Audi', 'model' => 'e-tron', 'category' => 'electric', 'image' => 'electric-audi-etron.jpg'],
        ];

        $vehicle = fake()->randomElement($vehicleData);

        return [
            'owner_id' => User::factory()->owner(),
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'year' => fake()->numberBetween(2018, 2024),
            'color' => fake()->randomElement(['Black', 'White', 'Silver', 'Blue', 'Red', 'Gray', 'Green']),
            'plate_number' => fake()->bothify('???-####'),
            'category' => $vehicle['category'],
            'fuel_type' => fake()->randomElement(['petrol', 'diesel', 'electric', 'hybrid']),
            'transmission' => fake()->randomElement(['manual', 'automatic', 'cvt']),
            'seats' => fake()->randomElement([2, 4, 5, 7, 8]),
            'doors' => fake()->randomElement([2, 4, 5]),
            'engine_size' => fake()->randomFloat(1, 1.0, 5.0),
            'mileage' => fake()->numberBetween(5000, 80000),
            'daily_rate' => fake()->numberBetween(30, 250),
            'location' => fake()->city(),
            'pickup_location' => fake()->address(),
            'is_available' => fake()->boolean(80),
            'insurance_included' => fake()->boolean(70),
            'insurance_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'description' => fake()->paragraph(3),
            'terms_and_conditions' => fake()->paragraph(2),
            'oil_type' => fake()->randomElement(['5W-30', '5W-40', '10W-30', '10W-40']),
            'last_oil_change' => fake()->optional(0.7)->dateTimeBetween('-1 year', '-1 month'),
            'policy' => fake()->optional(0.8)->paragraph(2),
            'featured_image' => 'vehicles/'.$vehicle['image'],
            'status' => fake()->randomElement(['pending', 'approved', 'published']),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }
}
