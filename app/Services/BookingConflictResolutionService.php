<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingConflictResolutionService
{
    /**
     * Detect and resolve booking conflicts for a vehicle
     */
    public function detectConflicts(int $vehicleId, string $startDate, string $endDate, ?int $excludeBookingId = null): array
    {
        $conflicts = $this->findConflictingBookings($vehicleId, $startDate, $endDate, $excludeBookingId);

        if ($conflicts->isEmpty()) {
            return [
                'has_conflicts' => false,
                'conflicts' => [],
                'resolution_options' => [],
            ];
        }

        $resolutionOptions = $this->generateResolutionOptions($vehicleId, $startDate, $endDate);

        return [
            'has_conflicts' => true,
            'conflicts' => $conflicts->map(fn ($booking): array => [
                'booking_id' => $booking->id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'renter_name' => $booking->renter->name ?? 'Unknown',
                'total_amount' => $booking->total_amount,
            ])->toArray(),
            'resolution_options' => $resolutionOptions,
        ];
    }

    /**
     * Find all bookings that conflict with the given date range
     */
    private function findConflictingBookings(int $vehicleId, string $startDate, string $endDate, ?int $excludeBookingId = null): Collection
    {
        $query = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'ongoing', 'pending'])
            ->whereNull('deleted_at')
            ->where(function ($dateQuery) use ($startDate, $endDate): void {
                $dateQuery->where(function ($overlap) use ($startDate): void {
                    // New booking starts during an existing booking
                    $overlap->where('start_date', '<=', $startDate)
                        ->where('end_date', '>', $startDate);
                })->orWhere(function ($overlap) use ($endDate): void {
                    // New booking ends during an existing booking
                    $overlap->where('start_date', '<', $endDate)
                        ->where('end_date', '>=', $endDate);
                })->orWhere(function ($overlap) use ($startDate, $endDate): void {
                    // New booking completely encompasses an existing booking
                    $overlap->where('start_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                })->orWhere(function ($overlap) use ($startDate, $endDate): void {
                    // Existing booking completely encompasses new booking
                    $overlap->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
            })
            ->with(['renter']);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get();
    }

    /**
     * Generate resolution options for booking conflicts
     */
    private function generateResolutionOptions(int $vehicleId, string $startDate, string $endDate): array
    {
        $options = [];

        // Option 1: Suggest alternative dates
        $alternativeDates = $this->findAlternativeDates($vehicleId, $startDate, $endDate);
        if ($alternativeDates !== []) {
            $options[] = [
                'type' => 'alternative_dates',
                'title' => 'Alternative Available Dates',
                'description' => 'We found similar time periods when this vehicle is available',
                'suggestions' => $alternativeDates,
            ];
        }

        // Option 2: Suggest similar vehicles
        $alternativeVehicles = $this->findSimilarAvailableVehicles($vehicleId, $startDate, $endDate);
        if ($alternativeVehicles !== []) {
            $options[] = [
                'type' => 'alternative_vehicles',
                'title' => 'Similar Available Vehicles',
                'description' => 'These vehicles have similar features and are available for your dates',
                'suggestions' => $alternativeVehicles,
            ];
        }

        // Option 3: Waitlist for cancellations
        $options[] = [
            'type' => 'waitlist',
            'title' => 'Join Waitlist',
            'description' => 'Get notified if any conflicting bookings are cancelled',
            'action' => 'join_waitlist',
        ];

        return $options;
    }

    /**
     * Find alternative available dates for the same vehicle
     */
    private function findAlternativeDates(int $vehicleId, string $startDate, string $endDate, int $maxSuggestions = 5): array
    {
        $requestedStart = Carbon::parse($startDate);
        $requestedEnd = Carbon::parse($endDate);
        $duration = $requestedStart->diffInDays($requestedEnd);

        $suggestions = [];
        $searchStart = Carbon::parse($startDate)->subDays(14); // Search 2 weeks before
        $searchEnd = Carbon::parse($endDate)->addDays(30); // Search 1 month after

        // Check availability for each day in the search range
        for ($date = $searchStart->copy(); $date->lte($searchEnd) && count($suggestions) < $maxSuggestions; $date->addDay()) {
            $potentialStart = $date->copy();
            $potentialEnd = $date->copy()->addDays($duration);

            // Skip if this overlaps with requested dates (we already know it conflicts)
            if ($potentialStart->between($requestedStart, $requestedEnd) ||
                $potentialEnd->between($requestedStart, $requestedEnd)) {
                continue;
            }

            // Check if this date range is available
            $conflicts = $this->findConflictingBookings(
                $vehicleId,
                $potentialStart->toDateString(),
                $potentialEnd->toDateString()
            );

            if ($conflicts->isEmpty()) {
                $suggestions[] = [
                    'start_date' => $potentialStart->toDateString(),
                    'end_date' => $potentialEnd->toDateString(),
                    'duration_days' => $duration + 1,
                    'date_difference' => $requestedStart->diffInDays($potentialStart, false), // Negative if before
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Find similar vehicles that are available for the requested dates
     */
    private function findSimilarAvailableVehicles(int $vehicleId, string $startDate, string $endDate, int $maxSuggestions = 3): array
    {
        $originalVehicle = Vehicle::findOrFail($vehicleId);

        // Find vehicles with similar characteristics
        $similarVehicles = Vehicle::query()
            ->where('id', '!=', $vehicleId)
            ->where('is_available', true)
            ->where('status', 'published')
            ->where(function ($query) use ($originalVehicle): void {
                // Same category or similar price range
                $query->where('category', $originalVehicle->category)
                    ->orWhereBetween('daily_rate', [
                        $originalVehicle->daily_rate * 0.8, // 20% less
                        $originalVehicle->daily_rate * 1.2, // 20% more
                    ]);
            })
            ->with(['owner', 'images'])
            ->withAvg('reviews', 'rating')
            ->take($maxSuggestions * 2) // Get more to filter later
            ->get();

        $availableVehicles = [];

        foreach ($similarVehicles as $similarVehicle) {
            // Check if this vehicle is available for the requested dates
            $conflicts = $this->findConflictingBookings($similarVehicle->id, $startDate, $endDate);

            if ($conflicts->isEmpty() && count($availableVehicles) < $maxSuggestions) {
                $availableVehicles[] = [
                    'vehicle_id' => $similarVehicle->id,
                    'make' => $similarVehicle->make,
                    'model' => $similarVehicle->model,
                    'year' => $similarVehicle->year,
                    'category' => $similarVehicle->category,
                    'daily_rate' => $similarVehicle->daily_rate,
                    'location' => $similarVehicle->location,
                    'rating' => $similarVehicle->reviews_avg_rating ? round($similarVehicle->reviews_avg_rating, 1) : null,
                    'image' => $similarVehicle->featured_image,
                    'price_difference' => $similarVehicle->daily_rate - $originalVehicle->daily_rate,
                ];
            }
        }

        return $availableVehicles;
    }

    /**
     * Handle expired or abandoned bookings
     */
    public function cleanupExpiredBookings(): int
    {
        $expiredCount = 0;

        DB::transaction(function () use (&$expiredCount): void {
            // Find bookings that are stuck in pending status with pending payment for more than 1 hour
            $expiredBookings = Booking::query()
                ->where('status', 'pending')
                ->where('payment_status', 'pending')
                ->where('created_at', '<', now()->subHour())
                ->lockForUpdate()
                ->get();

            foreach ($expiredBookings as $expiredBooking) {
                $expiredBooking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);

                Log::info('Expired booking cancelled', [
                    'booking_id' => $expiredBooking->id,
                    'vehicle_id' => $expiredBooking->vehicle_id,
                    'created_at' => $expiredBooking->created_at,
                ]);

                $expiredCount++;
            }
        });

        return $expiredCount;
    }

    /**
     * Handle booking conflicts when database constraints are triggered
     */
    public function handleDatabaseConflict(int $vehicleId, string $startDate, string $endDate): BookingException
    {
        $conflictInfo = $this->detectConflicts($vehicleId, $startDate, $endDate);

        $message = 'This vehicle is already booked for the selected dates. ';

        if (! empty($conflictInfo['resolution_options'])) {
            $alternativeDates = collect($conflictInfo['resolution_options'])
                ->where('type', 'alternative_dates')
                ->first();

            if ($alternativeDates && ! empty($alternativeDates['suggestions'])) {
                $firstAlternative = $alternativeDates['suggestions'][0];
                $message .= sprintf(
                    'Alternative dates available: %s to %s',
                    Carbon::parse($firstAlternative['start_date'])->format('M j, Y'),
                    Carbon::parse($firstAlternative['end_date'])->format('M j, Y')
                );
            }
        }

        return BookingException::dateConflict($vehicleId, $startDate, $endDate);
    }

    /**
     * Check if a booking update would create conflicts
     */
    public function validateBookingUpdate(int $bookingId, string $newStartDate, string $newEndDate): array
    {
        $booking = Booking::findOrFail($bookingId);

        return $this->detectConflicts(
            $booking->vehicle_id,
            $newStartDate,
            $newEndDate,
            $bookingId // Exclude the current booking from conflict check
        );
    }
}
