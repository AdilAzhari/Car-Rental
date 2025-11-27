<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Log;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * @property $faker
 */
class CarRentalSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Comprehensive seeder for edge cases and complex scenarios
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating comprehensive test scenarios and edge cases...');

        // Create edge case users
        $this->createEdgeCaseUsers();

        // Create vehicles with various edge cases
        $this->createEdgeCaseVehicles();

        // Create complex booking scenarios
        $this->createComplexBookingScenarios();

        // Create payment edge cases
        $this->createPaymentEdgeCases();

        // Create comprehensive logs
        $this->createComprehensiveLogs();

        $this->command->info('✅ Comprehensive seeding completed!');
    }

    private function createEdgeCaseUsers(): void
    {
        // User with extremely long name
        User::query()->firstOrCreate(['email' => 'long.name@example.com'], [
            'name' => 'Dr. Alexander Maximilian',
            'password' => Hash::make('password'),
            'phone' => '+1-555-LONG-NAME',
            'role' => 'owner',
            'license_number' => $this->faker->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
            'is_verified' => true,
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'address' => $this->faker->address(),
        ]);

        // User with special characters in name
        User::query()->firstOrCreate(['email' => 'special.chars@example.com'], [
            'name' => 'José María García-López',
            'password' => Hash::make('password'),
            'phone' => '+34-666-123-456',
            'role' => 'renter',
            'license_number' => $this->faker->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
            'is_verified' => true,
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'address' => $this->faker->address(),
        ]);

        // User with minimum age (18)
        User::query()->firstOrCreate(['email' => 'young@example.com'], [
            'name' => 'Young Renter',
            'password' => Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'role' => 'renter',
            'license_number' => $this->faker->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
            'is_verified' => true,
            'date_of_birth' => now()->subYears(18)->format('Y-m-d'),
            'address' => $this->faker->address(),
        ]);

        // User with maximum age (90)
        User::query()->firstOrCreate(['email' => 'senior@example.com'], [
            'name' => 'Senior Owner',
            'password' => Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'role' => 'owner',
            'license_number' => $this->faker->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
            'is_verified' => true,
            'date_of_birth' => now()->subYears(90)->format('Y-m-d'),
            'address' => $this->faker->address(),
        ]);

        // Create some additional edge case users only if they don't exist
        for ($i = 1; $i <= 3; $i++) {
            User::query()->firstOrCreate(['email' => "suspended.user{$i}@example.com"], [
                'name' => "Suspended User {$i}",
                'password' => Hash::make('password'),
                'phone' => $this->faker->phoneNumber(),
                'role' => 'renter',
                'license_number' => $this->faker->bothify('???-####-####'),
                'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
                'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
                'is_verified' => false,
                'email_verified_at' => null,
                'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
                'address' => $this->faker->address(),
            ]);
        }
    }

    private function createEdgeCaseVehicles(): void
    {
        $owners = User::query()->where('role', 'owner')->get();
        $admin = User::query()->where('role', 'admin')->first();

        if ($owners->isEmpty()) {
            return;
        }

        // Vehicle with maximum daily rate
        $expensiveVehicle = Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'make' => 'Lamborghini',
            'model' => 'Aventador',
            'year' => 2024,
            'daily_rate' => 500,
            'status' => 'published',
            'fuel_type' => 'petrol',
            'oil_type' => '10W-60',
            'policy' => 'Luxury supercar with extreme performance capabilities. Drivers must be 25+ with clean record.',
        ]);

        // Vehicle with minimum daily rate
        $cheapVehicle = Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'make' => 'Dacia',
            'model' => 'Logan',
            'year' => 2015,
            'daily_rate' => 15,
            'status' => 'published',
            'fuel_type' => 'petrol',
            'oil_type' => '5W-30',
            'policy' => 'Budget-friendly vehicle for economical transportation. Basic insurance included.',
        ]);

        // Very old vehicle
        $oldVehicle = Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'make' => 'Ford',
            'model' => 'Focus',
            'year' => 2005,
            'daily_rate' => 25,
            'status' => 'approved',
            'oil_type' => '5W-40',
            'policy' => 'High mileage vintage vehicle. Regular maintenance required.',
            'last_oil_change' => now()->subMonths(2),
        ]);

        // Electric vehicle with specific requirements
        $electricVehicle = Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'make' => 'Nissan',
            'model' => 'Leaf',
            'year' => 2022,
            'daily_rate' => 60,
            'status' => 'published',
            'fuel_type' => 'electric',
            'oil_type' => null, // Electric vehicles don't use oil
            'policy' => 'Eco-friendly electric vehicle with charging requirements. 200km range.',
        ]);

        // Vehicle with many images (edge case)
        $vehicleWithManyImages = Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'status' => 'published',
        ]);

        VehicleImage::factory(15)->create([
            'vehicle_id' => $vehicleWithManyImages->id,
        ]);

        // Vehicle with no images (edge case)
        Vehicle::factory()->create([
            'owner_id' => $owners->random()->id,
            'status' => 'pending',
        ]);

        // Add logs for these vehicles
        foreach ([$expensiveVehicle, $cheapVehicle, $oldVehicle, $electricVehicle, $vehicleWithManyImages] as $vehicle) {
            if ($vehicle->images()->exists()) {
                $vehicle->images()->first()->update(['is_primary' => true]);
            }

            Log::factory()->created()->create([
                'vehicle_id' => $vehicle->id,
                'user_id' => $vehicle->owner_id,
            ]);

            if ($vehicle->status !== 'pending' && $admin) {
                Log::factory()->approved()->create([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $admin->id,
                ]);
            }
        }
    }

    private function createComplexBookingScenarios(): void
    {
        $renters = User::query()->where('role', 'renter')->where('is_verified', true)->get();
        $vehicles = Vehicle::query()->where('status', 'published')->get();

        if ($renters->isEmpty() || $vehicles->isEmpty()) {
            return;
        }

        // Long-term booking (30 days)
        Booking::factory()->create([
            'vehicle_id' => $vehicles->random()->id,
            'renter_id' => $renters->random()->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(40),
            'status' => 'confirmed',
        ]);

        // Same-day booking
        Booking::factory()->create([
            'vehicle_id' => $vehicles->random()->id,
            'renter_id' => $renters->random()->id,
            'start_date' => now(),
            'end_date' => now(),
            'status' => 'confirmed',
        ]);

        // Cancelled booking
        Booking::factory()->create([
            'vehicle_id' => $vehicles->random()->id,
            'renter_id' => $renters->random()->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(8),
            'status' => 'cancelled',
        ]);

        // Booking with very high special requests
        Booking::factory()->create([
            'vehicle_id' => $vehicles->random()->id,
            'renter_id' => $renters->random()->id,
            'special_requests' => 'Please ensure the vehicle is thoroughly sanitized. I need child car seats for 2 children (ages 3 and 5). GPS navigation system must be updated with latest maps. Please provide emergency contact numbers for roadside assistance. Vehicle should be parked in covered area to avoid weather damage.',
        ]);

        // Overlapping booking attempt (should handle conflicts)
        $baseVehicle = $vehicles->first();
        Booking::factory()->create([
            'vehicle_id' => $baseVehicle->id,
            'renter_id' => $renters->random()->id,
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(18),
            'status' => 'confirmed',
        ]);

        // Attempt overlapping booking (should be handled by business logic)
        Booking::factory()->create([
            'vehicle_id' => $baseVehicle->id,
            'renter_id' => $renters->random()->id,
            'start_date' => now()->addDays(17),
            'end_date' => now()->addDays(20),
            'status' => 'pending',
        ]);
    }

    private function createPaymentEdgeCases(): void
    {
        $bookings = Booking::query()->whereIn('status', ['confirmed', 'ongoing', 'completed'])->get();

        foreach ($bookings->take(10) as $booking) {
            // Failed payment that was later successful
            Payment::factory()->create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_status' => 'failed',
                'payment_method' => 'credit',
                'created_at' => now()->subHours(2),
            ]);

            // Successful payment after failure
            Payment::factory()->create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_status' => 'confirmed',
                'payment_method' => 'visa',
                'transaction_id' => 'TXN-RETRY-'.$this->faker->bothify('#?#?#?#?'),
                'processed_at' => now()->subHour(),
            ]);
        }

        // Partial payment scenario
        $expensiveBooking = Booking::query()->where('total_amount', '>', 200)->first();
        if ($expensiveBooking) {
            Payment::factory()->create([
                'booking_id' => $expensiveBooking->id,
                'amount' => $expensiveBooking->total_amount * 0.5,
                'payment_status' => 'confirmed',
                'payment_method' => 'bank_transfer',
                'transaction_id' => 'TXN-PARTIAL-'.$this->faker->bothify('#?#?#?'),
            ]);
        }
    }

    private function createComprehensiveLogs(): void
    {
        $users = User::all();
        $vehicles = Vehicle::all();
        $admin = User::query()->where('role', 'admin')->first();

        // System-wide logs (assign to random vehicles for compliance with schema)
        $randomVehiclesForSystemLogs = $vehicles->random(min(8, $vehicles->count()));

        // System maintenance logs
        for ($i = 0; $i < 5; $i++) {
            Log::factory()->create([
                'user_id' => $admin?->id,
                'vehicle_id' => $randomVehiclesForSystemLogs->random()->id,
                'action' => 'system_maintenance',
                'description' => 'System maintenance performed: '.$this->faker->randomElement([
                    'Database optimization',
                    'Cache clearing',
                    'Security updates',
                    'Performance tuning',
                    'Backup verification',
                ]),
            ]);
        }

        // Bulk operations logs
        Log::factory()->create([
            'user_id' => $admin?->id,
            'vehicle_id' => $randomVehiclesForSystemLogs->random()->id,
            'action' => 'bulk_operation',
            'description' => 'Bulk vehicle status update: 25 vehicles approved',
        ]);

        // Security incident logs
        for ($i = 0; $i < 3; $i++) {
            Log::factory()->create([
                'vehicle_id' => $randomVehiclesForSystemLogs->random()->id,
                'action' => 'security_incident',
                'description' => $this->faker->randomElement([
                    'Multiple failed login attempts detected',
                    'Suspicious booking pattern flagged',
                    'Potential fraud attempt blocked',
                ]),
                'metadata' => [
                    'ip_address' => $this->faker->ipv4(),
                    'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
                    'source' => $this->faker->randomElement(['web', 'api', 'mobile']),
                ],
            ]);
        }

        // High-frequency user activity
        $activeUser = $users->where('role', 'renter')->first();
        if ($activeUser) {
            $randomVehicles = $vehicles->random(10); // Get some random vehicles for the logs
            for ($i = 0; $i < 20; $i++) {
                Log::factory()->create([
                    'user_id' => $activeUser->id,
                    'vehicle_id' => $randomVehicles->random()->id, // Always provide a vehicle_id
                    'action' => $this->faker->randomElement(['profile_view', 'search_performed', 'filter_applied']),
                    'description' => 'High-frequency user activity',
                    'metadata' => [
                        'ip_address' => $this->faker->ipv4(),
                        'session_duration' => random_int(30, 300),
                        'page_views' => random_int(1, 10),
                    ],
                    'created_at' => now()->subMinutes(random_int(1, 60)),
                ]);
            }
        }
    }
}
