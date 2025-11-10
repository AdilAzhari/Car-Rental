<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingConflictResolutionService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleAvailabilityController extends Controller
{
    public function __construct(
        private readonly BookingConflictResolutionService $bookingConflictResolutionService
    ) {}

    /**
     * Get vehicle availability calendar data for a specific month/period
     */
    public function getAvailabilityCalendar(Request $request, int $vehicleId): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timezone' => 'sometimes|string',
        ]);

        try {
            $vehicle = Vehicle::findOrFail($vehicleId);
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get blocked date ranges
            $blockedRanges = $this->getBlockedDateRanges($vehicleId, $startDate, $endDate);

            // Generate day-by-day availability
            $availability = $this->generateDailyAvailability($startDate, $endDate, $blockedRanges);

            // Get alternative suggestions if any conflicts exist
            $alternatives = $this->getAlternativeAvailability($vehicleId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'vehicle_id' => $vehicleId,
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'availability' => $availability,
                'blocked_ranges' => $blockedRanges,
                'alternatives' => $alternatives,
                'metadata' => [
                    'vehicle_name' => "{$vehicle->make} {$vehicle->model}",
                    'daily_rate' => $vehicle->daily_rate,
                    'is_available' => $vehicle->is_available,
                    'total_days' => $startDate->diffInDays($endDate) + 1,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get vehicle availability calendar', [
                'vehicle_id' => $vehicleId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicle availability.',
            ], 500);
        }
    }

    /**
     * Check specific date range availability
     */
    public function checkDateRangeAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|integer|exists:car_rental_vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $conflicts = $this->bookingConflictResolutionService->detectConflicts(
                $request->vehicle_id,
                $request->start_date,
                $request->end_date
            );

            $isAvailable = ! $conflicts['has_conflicts'];
            $blockedDates = [];

            if ($conflicts['has_conflicts']) {
                foreach ($conflicts['conflicts'] as $conflict) {
                    $period = CarbonPeriod::create($conflict['start_date'], $conflict['end_date']);
                    foreach ($period as $date) {
                        $blockedDates[] = $date->toDateString();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'is_available' => $isAvailable,
                'has_conflicts' => $conflicts['has_conflicts'],
                'blocked_dates' => array_unique($blockedDates),
                'conflicts' => $conflicts['conflicts'],
                'resolution_options' => $conflicts['resolution_options'],
                'requested_period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'duration_days' => Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check date range availability', [
                'vehicle_id' => $request->vehicle_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability for selected dates.',
            ], 500);
        }
    }

    /**
     * Get next available dates for a vehicle
     */
    public function getNextAvailableDates(Request $request, int $vehicleId): JsonResponse
    {
        $request->validate([
            'duration_days' => 'required|integer|min:1|max:365',
            'preferred_start_date' => 'sometimes|date|after:today',
            'max_suggestions' => 'sometimes|integer|min:1|max:20',
        ]);

        try {
            $durationDays = $request->duration_days;
            $preferredStart = $request->preferred_start_date ? Carbon::parse($request->preferred_start_date) : Carbon::tomorrow();
            $maxSuggestions = $request->max_suggestions ?? 10;

            $suggestions = $this->findNextAvailablePeriods(
                $vehicleId,
                $durationDays,
                $preferredStart,
                $maxSuggestions
            );

            return response()->json([
                'success' => true,
                'vehicle_id' => $vehicleId,
                'duration_days' => $durationDays,
                'preferred_start_date' => $preferredStart->toDateString(),
                'suggestions' => $suggestions,
                'total_suggestions' => count($suggestions),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get next available dates', [
                'vehicle_id' => $vehicleId,
                'duration_days' => $request->duration_days,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to find available dates.',
            ], 500);
        }
    }

    /**
     * Get blocked date ranges for a vehicle
     */
    private function getBlockedDateRanges(int $vehicleId, Carbon $startDate, Carbon $endDate): array
    {
        $bookings = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'ongoing', 'pending'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($overlapQuery) use ($startDate, $endDate): void {
                        $overlapQuery->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->orderBy('start_date')
            ->get();

        $blockedRanges = [];
        foreach ($bookings as $booking) {
            $blockedRanges[] = [
                'booking_id' => $booking->id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'duration_days' => Carbon::parse($booking->start_date)->diffInDays(Carbon::parse($booking->end_date)) + 1,
                'renter_name' => $booking->renter ? $booking->renter->name : 'Unknown',
            ];
        }

        return $blockedRanges;
    }

    /**
     * Generate day-by-day availability status
     */
    private function generateDailyAvailability(Carbon $startDate, Carbon $endDate, array $blockedRanges): array
    {
        $availability = [];
        $carbonPeriod = CarbonPeriod::create($startDate, $endDate);

        foreach ($carbonPeriod as $date) {
            $dateString = $date->toDateString();
            $isBlocked = false;
            $blockingBooking = null;

            // Check if this date is blocked by any booking
            foreach ($blockedRanges as $blockedRange) {
                $rangeStart = Carbon::parse($blockedRange['start_date']);
                $rangeEnd = Carbon::parse($blockedRange['end_date']);

                if ($date->between($rangeStart, $rangeEnd)) {
                    $isBlocked = true;
                    $blockingBooking = $blockedRange['booking_id'];
                    break;
                }
            }

            $availability[] = [
                'date' => $dateString,
                'day_name' => $date->format('l'),
                'is_available' => ! $isBlocked,
                'is_blocked' => $isBlocked,
                'is_weekend' => $date->isWeekend(),
                'is_past' => $date->isPast(),
                'blocking_booking_id' => $blockingBooking,
            ];
        }

        return $availability;
    }

    /**
     * Get alternative availability suggestions
     */
    private function getAlternativeAvailability(int $vehicleId, Carbon $startDate, Carbon $endDate): array
    {
        $durationDays = $startDate->diffInDays($endDate) + 1;

        // Find alternative periods before and after the requested dates
        $alternatives = $this->findNextAvailablePeriods($vehicleId, $durationDays, $startDate->copy()->subDays(30), 5);

        return $alternatives;
    }

    /**
     * Find next available periods for a given duration
     */
    private function findNextAvailablePeriods(int $vehicleId, int $durationDays, Carbon $searchStart, int $maxSuggestions): array
    {
        $suggestions = [];
        $searchEnd = $searchStart->copy()->addYear(); // Search within 1 year
        $currentDate = $searchStart->copy();

        while ($currentDate->lte($searchEnd) && count($suggestions) < $maxSuggestions) {
            $periodEnd = $currentDate->copy()->addDays($durationDays - 1);

            // Check if this period is available
            $conflicts = $this->bookingConflictResolutionService->detectConflicts(
                $vehicleId,
                $currentDate->toDateString(),
                $periodEnd->toDateString()
            );

            if (! $conflicts['has_conflicts']) {
                $suggestions[] = [
                    'start_date' => $currentDate->toDateString(),
                    'end_date' => $periodEnd->toDateString(),
                    'duration_days' => $durationDays,
                    'days_from_preferred' => $searchStart->diffInDays($currentDate, false),
                    'day_names' => [
                        'start' => $currentDate->format('l, M j'),
                        'end' => $periodEnd->format('l, M j'),
                    ],
                    'is_weekend_start' => $currentDate->isWeekend(),
                    'is_available' => true,
                ];
            }

            $currentDate->addDay();
        }

        return $suggestions;
    }

    /**
     * Get vehicle availability summary (used for listings)
     */
    public function getAvailabilitySummary(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_ids' => 'required|array',
            'vehicle_ids.*' => 'integer|exists:car_rental_vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $vehicleIds = $request->vehicle_ids;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $summary = [];

            foreach ($vehicleIds as $vehicleId) {
                $conflicts = $this->bookingConflictResolutionService->detectConflicts($vehicleId, $startDate, $endDate);

                $summary[] = [
                    'vehicle_id' => $vehicleId,
                    'is_available' => ! $conflicts['has_conflicts'],
                    'has_conflicts' => $conflicts['has_conflicts'],
                    'conflict_count' => count($conflicts['conflicts']),
                    'alternative_count' => count($conflicts['resolution_options']),
                ];
            }

            return response()->json([
                'success' => true,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'vehicles' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get availability summary', [
                'vehicle_ids' => $request->vehicle_ids,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get availability summary.',
            ], 500);
        }
    }
}
