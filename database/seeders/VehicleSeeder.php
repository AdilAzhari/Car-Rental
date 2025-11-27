<?php

namespace Database\Seeders;

use App\Models\Log;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function run(): void
    {
        $owners = User::query()->where('role', 'owner')->get();
        $admin = User::query()->where('role', 'admin')->first();

        if ($owners->isEmpty()) {
            $this->command->warn('No owners found. Run UserSeeder first.');

            return;
        }

        // Create specific test vehicles with different statuses
        $testVehicles = [
            [
                'owner_email' => 'john.smith@example.com',
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => 2022,
                'plate_number' => 'ABC-1s234',
                'daily_rate' => 45,
                'status' => 'published',
                'fuel_type' => 'petrol',
                'transmission' => 'automatic',
                'oil_type' => '5W-30',
            ],
            [
                'owner_email' => 'sarah.johnson@example.com',
                'make' => 'BMW',
                'model' => '3 Series',
                'year' => 2023,
                'plate_number' => 'XYZ-53678',
                'daily_rate' => 75,
                'status' => 'published',
                'fuel_type' => 'petrol',
                'transmission' => 'automatic',
                'oil_type' => '5W-40',
            ],
            [
                'owner_email' => 'michael.brown@example.com',
                'make' => 'Honda',
                'model' => 'Civic',
                'year' => 2021,
                'plate_number' => 'DEF-90312',
                'daily_rate' => 40,
                'status' => 'pending', // Pending approval
                'fuel_type' => 'petrol',
                'transmission' => 'manual',
                'oil_type' => '5W-30',
            ],
            [
                'owner_email' => 'john.smith@example.com',
                'make' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2023,
                'plate_number' => 'ELC-2024',
                'daily_rate' => 90,
                'status' => 'approved', // Approved but not published
                'fuel_type' => 'electric',
                'transmission' => 'automatic',
                'oil_type' => null, // Electric vehicles don't need oil
            ],
        ];

        foreach ($testVehicles as $testVehicle) {
            $owner = User::query()->where('email', $testVehicle['owner_email'])->first();
            if (! $owner) {
                continue;
            }

            // Remove owner_email from the data array since it's not a database column
            $cleanVehicleData = $testVehicle;
            unset($cleanVehicleData['owner_email']);

            $vehicle = Vehicle::query()->firstOrCreate(['plate_number' => $testVehicle['plate_number']], array_merge($cleanVehicleData, [
                'owner_id' => $owner->id,
                'last_oil_change' => $testVehicle['fuel_type'] !== 'electric' ? $this->faker->dateTimeBetween('-6 months', '-1 month') : null,
                'policy' => $this->faker->paragraph(2),
            ]));

            // Add images for each vehicle
            VehicleImage::factory(random_int(3, 6))->create([
                'vehicle_id' => $vehicle->id,
            ]);

            // Set first image as primary
            $vehicle->images()->first()?->update(['is_primary' => true]);

            // Add creation log
            Log::factory()->created()->create([
                'vehicle_id' => $vehicle->id,
                'user_id' => $vehicle->owner_id,
            ]);

            // Add approval log for non-pending vehicles
            if ($vehicle->status !== 'pending' && $admin) {
                Log::factory()->approved()->create([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $admin->id,
                ]);
            }
        }

        // Create additional random vehicles for each owner
        foreach ($owners as $owner) {
            $vehicleCount = random_int(1, 3);
            $vehicles = Vehicle::factory($vehicleCount)->create(['owner_id' => $owner->id]);

            foreach ($vehicles as $vehicle) {
                // Add images
                VehicleImage::factory(random_int(2, 5))->create([
                    'vehicle_id' => $vehicle->id,
                ]);

                // Set primary image
                $vehicle->images()->first()?->update(['is_primary' => true]);

                // Add maintenance logs
                Log::factory(random_int(1, 3))->create([
                    'vehicle_id' => $vehicle->id,
                ]);

                // Add creation log
                Log::factory()->created()->create([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $vehicle->owner_id,
                ]);

                // Add approval log for approved/published vehicles
                if (in_array($vehicle->status, ['approved', 'published']) && $admin) {
                    Log::factory()->approved()->create([
                        'vehicle_id' => $vehicle->id,
                        'user_id' => $admin->id,
                    ]);
                }
            }
        }
    }
}
