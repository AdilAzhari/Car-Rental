<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    public function definition(): array
    {
        $actions = [
            'created' => 'Vehicle was created and is pending approval',
            'approved' => 'Vehicle was approved by admin',
            'rejected' => 'Vehicle was rejected by admin',
            'published' => 'Vehicle was published and is now available for rent',
            'booked' => 'Vehicle was booked by a renter',
            'returned' => 'Vehicle was returned by the renter',
            'maintenance' => 'Vehicle is under maintenance',
            'updated' => 'Vehicle information was updated',
            'viewed' => 'Vehicle details were viewed',
            'favorited' => 'Vehicle was added to favorites',
            'reported' => 'Vehicle was reported for issues',
            'suspended' => 'Vehicle was suspended',
            'reactivated' => 'Vehicle was reactivated',
            'deleted' => 'Vehicle was deleted',
            'image_uploaded' => 'New image was uploaded for vehicle',
            'price_updated' => 'Vehicle daily rate was updated',
        ];

        $action = $this->faker->randomKey($actions);
        $userAgents = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
        ];

        return [
            'vehicle_id' => Vehicle::factory(), // Always require a vehicle
            'user_id' => $this->faker->boolean(75) ? User::factory() : null,
            'action' => $action,
            'description' => $actions[$action],
            'metadata' => $this->faker->optional(0.4)->passthrough([
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->randomElement($userAgents),
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'location' => $this->faker->city(),
                'session_id' => $this->faker->uuid(),
                'previous_value' => $this->faker->optional()->word(),
                'new_value' => $this->faker->optional()->word(),
            ]),
        ];
    }

    public function created(): static
    {
        return $this->state([
            'action' => 'created',
            'description' => 'Vehicle was created and is pending approval',
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'action' => 'approved',
            'description' => 'Vehicle was approved by admin',
        ]);
    }

    public function booked(): static
    {
        return $this->state([
            'action' => 'booked',
            'description' => 'Vehicle was booked by a renter',
        ]);
    }
}
