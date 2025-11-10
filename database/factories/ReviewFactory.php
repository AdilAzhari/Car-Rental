<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);
        $comments = [
            1 => [
                'Terrible experience. Would not recommend to anyone.',
                'Car broke down during our trip. Very disappointing.',
                'Poor communication from owner. Vehicle was dirty.',
                'Not as described. Many issues with the car.',
            ],
            2 => [
                'Car had several issues. Not worth the price.',
                'Below average experience. Expected much better.',
                'Vehicle was okay but had some problems.',
                'Poor customer service from the owner.',
            ],
            3 => [
                'Average rental experience. Nothing special.',
                'Car was fine but nothing exceptional.',
                'Decent vehicle for the price point.',
                'Standard rental, no major issues.',
            ],
            4 => [
                'Good car and service. Would rent again.',
                'Nice vehicle, clean and well-maintained.',
                'Pleasant experience overall. Recommended.',
                'Good value for money. Owner was helpful.',
            ],
            5 => [
                'Excellent experience! Perfect car and great owner.',
                'Outstanding service! Car was immaculate.',
                'Fantastic rental! Exceeded all expectations.',
                'Perfect car for our needs. Highly recommended!',
            ],
        ];

        $detailedComments = [
            1 => 'The vehicle had multiple mechanical issues during our rental period. The air conditioning was not working properly, and there was a strange noise coming from the engine. The owner was unresponsive when we tried to contact them about these problems.',
            2 => 'While the car was functional, it was not clean when we picked it up. There were stains on the seats and the fuel tank was not full as promised. The pickup process was also delayed by 30 minutes.',
            3 => 'The rental went smoothly overall. The car served its purpose for our city trip, though it showed signs of wear and tear. The owner was polite and the transaction was straightforward.',
            4 => 'Great experience renting this vehicle! It was clean, comfortable, and perfect for our weekend getaway. The owner was very communicative and made the pickup/dropoff process seamless. Would definitely rent again.',
            5 => 'This was hands down the best car rental experience we\'ve had! The vehicle was spotless, smelled fresh, and drove like a dream. The owner went above and beyond by providing phone chargers, water bottles, and even local recommendations. Exceptional service!',
        ];

        return [
            'booking_id' => Booking::factory(),
            'vehicle_id' => Vehicle::factory(),
            'renter_id' => User::factory()->renter(),
            'rating' => $rating,
            'comment' => fake()->optional(0.7)->randomElement($detailedComments) ?? fake()->randomElement($comments[$rating]),
            'is_visible' => fake()->boolean(85),
        ];
    }

    public function excellent(): static
    {
        return $this->state([
            'rating' => 5,
            'comment' => fake()->randomElement([
                'Outstanding vehicle! Clean, reliable, and the owner was very helpful.',
                'Perfect car for our trip. Everything was exactly as described.',
                'Excellent experience from start to finish. Highly recommended!',
            ]),
        ]);
    }

    public function poor(): static
    {
        return $this->state([
            'rating' => fake()->numberBetween(1, 2),
            'comment' => fake()->randomElement([
                'Car had mechanical issues during our trip.',
                'Not as described. Very disappointed with the experience.',
                'Poor communication from the owner. Would not rent again.',
            ]),
        ]);
    }
}
