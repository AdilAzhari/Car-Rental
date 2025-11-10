<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating review sample data...');

        // Get completed bookings that don't have reviews yet
        $completedBookings = Booking::where('status', 'completed')
            ->doesntHave('review')
            ->with(['renter', 'vehicle'])
            ->limit(50)
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('No completed bookings found without reviews. Creating some sample bookings first...');

            // Create some completed bookings for testing
            $renters = User::where('role', 'renter')->limit(10)->get();

            if ($renters->isNotEmpty()) {
                foreach ($renters->take(5) as $renter) {
                    $booking = Booking::create([
                        'renter_id' => $renter->id,
                        'vehicle_id' => \App\Models\Vehicle::inRandomOrder()->first()?->id ?? 1,
                        'start_date' => now()->subDays(random_int(30, 90)),
                        'end_date' => now()->subDays(random_int(1, 29)),
                        'status' => 'completed',
                        'payment_status' => 'paid',
                        'total_amount' => random_int(200, 1000),
                    ]);

                    $completedBookings->push($booking);
                }
            }
        }

        $reviewTexts = [
            // 5-star reviews
            [
                'rating' => 5,
                'comments' => [
                    'Absolutely fantastic experience! The car was in perfect condition, clean, and exactly as described. The owner was very helpful and responsive. I would definitely rent from them again.',
                    'Outstanding service from start to finish. The vehicle was spotless and ran perfectly throughout my trip. Highly recommended for anyone looking for a reliable rental experience.',
                    'Exceeded all my expectations! The car was newer than I thought it would be, very clean, and the owner provided excellent customer service. Will definitely book again.',
                    'Perfect rental experience! The car was in excellent condition, pickup and drop-off were smooth, and the owner was very accommodating. Five stars!',
                    'Amazing experience! The vehicle was exactly what I needed for my business trip. Clean, reliable, and great value for money. Thank you!',
                ],
            ],
            // 4-star reviews
            [
                'rating' => 4,
                'comments' => [
                    'Great car and good service overall. The vehicle was clean and ran well. Only minor issue was a small delay during pickup, but everything else was perfect.',
                    'Very satisfied with my rental. The car was in good condition and served its purpose well. Would recommend to others looking for affordable options.',
                    'Good experience overall. The car was reliable and the owner was helpful. Just wish there were more features included, but still a solid choice.',
                    'Nice vehicle and smooth transaction. Everything went as expected. The car was clean and comfortable for my weekend trip.',
                    'Solid rental experience. The car performed well and was reasonably priced. Communication with the owner was good throughout.',
                ],
            ],
            // 3-star reviews
            [
                'rating' => 3,
                'comments' => [
                    'Average experience. The car was okay but showed some signs of wear. It got me where I needed to go without major issues.',
                    'Decent rental for the price. The vehicle was functional but not particularly impressive. Adequate for basic transportation needs.',
                    'Mixed experience. The car itself was fine, but there were some communication issues with the owner. Still completed my trip successfully.',
                    'The car was acceptable for my needs. Some minor maintenance issues but nothing that affected the rental significantly.',
                    'Fair rental experience. The vehicle served its purpose though it could have been cleaner. Average overall.',
                ],
            ],
            // 2-star reviews
            [
                'rating' => 2,
                'comments' => [
                    'Below expectations. The car had several issues including a check engine light that came on during my trip. Not very reassuring.',
                    'Disappointing experience. The vehicle was not as clean as expected and had some mechanical concerns. Would look elsewhere next time.',
                    'The car had some problems that made the rental stressful. Communication with the owner could have been better when issues arose.',
                ],
            ],
            // 1-star reviews
            [
                'rating' => 1,
                'comments' => [
                    'Very poor experience. The car broke down during my rental period and caused significant inconvenience. Would not recommend.',
                    'Terrible rental. The vehicle was in poor condition and the owner was unresponsive when I had problems. Avoid this rental.',
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($completedBookings as $completedBooking) {
            if (! $completedBooking->renter || ! $completedBooking->vehicle) {
                continue;
            }

            // Weighted random rating (more 4-5 star reviews)
            $ratingWeights = [5 => 40, 4 => 30, 3 => 20, 2 => 7, 1 => 3];
            $randomNum = random_int(1, 100);
            $rating = 5;
            $cumulative = 0;

            foreach ($ratingWeights as $r => $weight) {
                $cumulative += $weight;
                if ($randomNum <= $cumulative) {
                    $rating = $r;
                    break;
                }
            }

            $ratingGroup = collect($reviewTexts)->firstWhere('rating', $rating);
            $comment = $ratingGroup['comments'][array_rand($ratingGroup['comments'])];

            Review::create([
                'booking_id' => $completedBooking->id,
                'vehicle_id' => $completedBooking->vehicle_id,
                'renter_id' => $completedBooking->renter_id,
                'rating' => $rating,
                'comment' => $comment,
                'is_visible' => random_int(1, 100) > 5, // 95% visible
                'created_at' => $completedBooking->end_date->addDays(random_int(1, 14)), // Review 1-14 days after booking end
                'updated_at' => now(),
            ]);

            $createdCount++;

            if ($createdCount >= 30) {
                break; // Limit to 30 reviews for testing
            }
        }

        $this->command->info("✅ Created {$createdCount} sample reviews successfully!");

        // Display statistics
        $totalReviews = Review::count();
        $averageRating = Review::avg('rating');
        $ratingDistribution = Review::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        $this->command->info('📊 Review Statistics:');
        $this->command->info("   Total Reviews: {$totalReviews}");
        $this->command->info('   Average Rating: '.number_format($averageRating, 2).'/5');
        $this->command->info('   Rating Distribution:');

        foreach ($ratingDistribution as $dist) {
            $stars = str_repeat('⭐', $dist->rating);
            $this->command->info("   {$stars} ({$dist->rating}): {$dist->count} reviews");
        }
    }
}
