<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Car Rental System Database Seeding...');

        $this->call([
            UserSeeder::class,
            VehicleSeeder::class,
            BookingSeeder::class,
            CarRentalSeeder::class, // Keep for any additional comprehensive seeding
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
