<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Log;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $renters = User::query()->where('role', 'renter')->where('is_verified', true)->get();
        $publishedVehicles = Vehicle::query()->where('status', 'published')->get();

        if ($renters->isEmpty() || $publishedVehicles->isEmpty()) {
            $this->command->warn('No verified renters or published vehicles found. Run UserSeeder and VehicleSeeder first.');

            return;
        }

        // Create specific test bookings with different scenarios
        $testBookings = [
            [
                'renter_email' => 'emma.wilson@example.com',
                'vehicle_plate' => 'ABC-1234',
                'status' => 'completed',
                'start_date' => now()->subDays(15),
                'end_date' => now()->subDays(10),
                'create_payment' => true,
                'create_review' => true,
                'review_rating' => 5,
            ],
            [
                'renter_email' => 'david.lee@example.com',
                'vehicle_plate' => 'XYZ-5678',
                'status' => 'ongoing',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(3),
                'create_payment' => true,
                'create_review' => false,
            ],
            [
                'renter_email' => 'emma.wilson@example.com',
                'vehicle_plate' => 'ABC-1234',
                'status' => 'confirmed',
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(8),
                'create_payment' => true,
                'create_review' => false,
            ],
            [
                'renter_email' => 'david.lee@example.com',
                'vehicle_plate' => 'XYZ-5678',
                'status' => 'pending',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(14),
                'create_payment' => false,
                'create_review' => false,
            ],
            [
                'renter_email' => 'lisa.davis@example.com',
                'vehicle_plate' => 'ABC-1234',
                'status' => 'completed',
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(25),
                'create_payment' => true,
                'create_review' => true,
                'review_rating' => 3,
            ],
        ];

        foreach ($testBookings as $testBooking) {
            $renter = User::query()->where('email', $testBooking['renter_email'])->first();
            $vehicle = Vehicle::query()->where('plate_number', $testBooking['vehicle_plate'])->first();

            if (! $renter || ! $vehicle) {
                continue;
            }

            $days = Carbon::parse($testBooking['start_date'])->diffInDays($testBooking['end_date']) + 1;
            $totalAmount = $vehicle->daily_rate * $days;
            $depositAmount = $totalAmount * 0.2;
            $commissionAmount = $totalAmount * 0.15;

            // Remove non-database fields from booking data
            $cleanBookingData = $testBooking;
            unset($cleanBookingData['renter_email'], $cleanBookingData['vehicle_plate'], $cleanBookingData['create_payment'], $cleanBookingData['create_review'], $cleanBookingData['review_rating']);

            $booking = Booking::query()->firstOrCreate([
                'renter_id' => $renter->id,
                'vehicle_id' => $vehicle->id,
                'start_date' => $testBooking['start_date'],
                'end_date' => $testBooking['end_date'],
            ], array_merge($cleanBookingData, [
                'renter_id' => $renter->id,
                'vehicle_id' => $vehicle->id,
                'total_amount' => $totalAmount,
                'pickup_location' => fake()->address(),
                'dropoff_location' => fake()->address(),
                'special_requests' => $cleanBookingData['special_requests'] ?? fake()->optional(0.3)->paragraph(2),
                'deposit_amount' => $depositAmount,
                'commission_amount' => $commissionAmount,
            ]));

            // Create payment if specified
            if ($testBooking['create_payment']) {
                Payment::query()->firstOrCreate([
                    'booking_id' => $booking->id,
                ], [
                    'amount' => $booking->total_amount,
                    'payment_method' => fake()->randomElement(['visa', 'credit', 'cash', 'bank_transfer']),
                    'payment_status' => in_array($booking->status, ['confirmed', 'ongoing', 'completed']) ? 'confirmed' : 'pending',
                    'transaction_id' => 'TXN-'.fake()->bothify('#?#?#?#?#?#?'),
                    'processed_at' => $booking->status === 'pending' ? null : now(),
                ]);
            }

            // Create review if specified and booking is completed
            if ($testBooking['create_review'] && $booking->status === 'completed') {
                Review::query()->firstOrCreate([
                    'booking_id' => $booking->id,
                ], [
                    'vehicle_id' => $booking->vehicle_id,
                    'renter_id' => $booking->renter_id,
                    'rating' => $testBooking['review_rating'] ?? fake()->numberBetween(3, 5),
                    'comment' => fake()->paragraph(2),
                ]);
            }

            // Create booking logs
            Log::factory()->create([
                'user_id' => $renter->id,
                'vehicle_id' => $vehicle->id,
                'action' => 'booking_created',
                'description' => "Booking created for $vehicle->make $vehicle->model",
                'metadata' => [
                    'ip_address' => fake()->ipv4(),
                    'booking_id' => $booking->id,
                ],
            ]);

            if (in_array($booking->status, ['confirmed', 'ongoing', 'completed'])) {
                Log::factory()->create([
                    'user_id' => $vehicle->owner_id,
                    'vehicle_id' => $vehicle->id,
                    'action' => 'booking_confirmed',
                    'description' => "Booking confirmed for $vehicle->make $vehicle->model",
                    'metadata' => [
                        'ip_address' => fake()->ipv4(),
                        'booking_id' => $booking->id,
                    ],
                ]);
            }
        }

        // Create additional random bookings
        $additionalBookings = min(30, $publishedVehicles->count() * 2);

        for ($i = 0; $i < $additionalBookings; $i++) {
            $vehicle = $publishedVehicles->random();
            $renter = $renters->random();

            // Avoid duplicate bookings for same vehicle and dates
            $startDate = fake()->dateTimeBetween('-2 months', '+2 months');
            $endDate = Carbon::parse($startDate)->addDays(fake()->numberBetween(1, 10));

            $existingBooking = Booking::query()->where('vehicle_id', $vehicle->id)
                ->where(function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate): void {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($existingBooking) {
                continue;
            }

            $booking = Booking::factory()->create([
                'vehicle_id' => $vehicle->id,
                'renter_id' => $renter->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Create payment for most bookings
            if (fake()->boolean(85)) {
                Payment::factory()->create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_amount,
                    'payment_status' => in_array($booking->status, ['confirmed', 'ongoing', 'completed']) ? 'confirmed' : fake()->randomElement(['pending', 'failed']),
                ]);
            }

            // Create reviews for completed bookings
            if ($booking->status === 'completed' && fake()->boolean(70)) {
                Review::factory()->create([
                    'booking_id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'renter_id' => $booking->renter_id,
                ]);
            }
        }
    }
}
