<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', '+3 months');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 14));
        $dailyRate = $this->faker->numberBetween(30, 150);
        $days = Carbon::parse($startDate)->diffInDays($endDate) + 1;
        $totalAmount = $dailyRate * $days;
        $depositAmount = $totalAmount * 0.2;
        $commissionAmount = $totalAmount * 0.15;

        return [
            'renter_id' => User::factory()->renter(),
            'vehicle_id' => Vehicle::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => $totalAmount,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'ongoing', 'completed']),
            'pickup_location' => $this->faker->address(),
            'dropoff_location' => $this->faker->address(),
            'special_requests' => $this->faker->optional()->paragraph(2),
            'deposit_amount' => $depositAmount,
            'commission_amount' => $commissionAmount,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function ongoing(): static
    {
        return $this->state([
            'status' => 'ongoing',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(3),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(3),
        ]);
    }
}
