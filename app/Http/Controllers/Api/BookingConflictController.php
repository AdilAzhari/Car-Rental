<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingConflictResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingConflictController extends Controller
{
    public function __construct(
        private readonly BookingConflictResolutionService $bookingConflictResolutionService
    ) {}

    /**
     * Check for booking conflicts and get resolution options
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|integer|exists:car_rental_vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'exclude_booking_id' => 'sometimes|integer|exists:car_rental_bookings,id',
        ]);

        try {
            $conflictInfo = $this->bookingConflictResolutionService->detectConflicts(
                $request->vehicle_id,
                $request->start_date,
                $request->end_date,
                $request->exclude_booking_id
            );

            return response()->json([
                'success' => true,
                'has_conflicts' => $conflictInfo['has_conflicts'],
                'conflicts' => $conflictInfo['conflicts'],
                'resolution_options' => $conflictInfo['resolution_options'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check booking conflicts', [
                'vehicle_id' => $request->vehicle_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check for booking conflicts.',
            ], 500);
        }
    }

    /**
     * Get alternative available dates for a vehicle
     */
    public function getAlternativeDates(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|integer|exists:car_rental_vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'max_suggestions' => 'sometimes|integer|min:1|max:10',
        ]);

        try {
            $conflictInfo = $this->bookingConflictResolutionService->detectConflicts(
                $request->vehicle_id,
                $request->start_date,
                $request->end_date
            );

            $alternativeDates = collect($conflictInfo['resolution_options'])
                ->where('type', 'alternative_dates')
                ->first();

            return response()->json([
                'success' => true,
                'has_alternatives' => ! empty($alternativeDates['suggestions']),
                'suggestions' => $alternativeDates['suggestions'] ?? [],
                'total_alternatives' => count($alternativeDates['suggestions'] ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get alternative dates', [
                'vehicle_id' => $request->vehicle_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to find alternative dates.',
            ], 500);
        }
    }

    /**
     * Get similar available vehicles
     */
    public function getSimilarVehicles(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|integer|exists:car_rental_vehicles,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'max_suggestions' => 'sometimes|integer|min:1|max:10',
        ]);

        try {
            $conflictInfo = $this->bookingConflictResolutionService->detectConflicts(
                $request->vehicle_id,
                $request->start_date,
                $request->end_date
            );

            $alternativeVehicles = collect($conflictInfo['resolution_options'])
                ->where('type', 'alternative_vehicles')
                ->first();

            return response()->json([
                'success' => true,
                'has_alternatives' => ! empty($alternativeVehicles['suggestions']),
                'suggestions' => $alternativeVehicles['suggestions'] ?? [],
                'total_alternatives' => count($alternativeVehicles['suggestions'] ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get similar vehicles', [
                'vehicle_id' => $request->vehicle_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to find similar vehicles.',
            ], 500);
        }
    }

    /**
     * Validate booking update for conflicts
     */
    public function validateBookingUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:car_rental_bookings,id',
            'new_start_date' => 'required|date|after:today',
            'new_end_date' => 'required|date|after:new_start_date',
        ]);

        try {
            $conflictInfo = $this->bookingConflictResolutionService->validateBookingUpdate(
                $request->booking_id,
                $request->new_start_date,
                $request->new_end_date
            );

            return response()->json([
                'success' => true,
                'can_update' => ! $conflictInfo['has_conflicts'],
                'has_conflicts' => $conflictInfo['has_conflicts'],
                'conflicts' => $conflictInfo['conflicts'],
                'resolution_options' => $conflictInfo['resolution_options'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to validate booking update', [
                'booking_id' => $request->booking_id,
                'new_start_date' => $request->new_start_date,
                'new_end_date' => $request->new_end_date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate booking update.',
            ], 500);
        }
    }

    /**
     * Get booking statistics for admin dashboard
     */
    public function getConflictStatistics(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_conflicts_last_30_days' => \App\Models\Booking::query()
                    ->where('status', 'cancelled')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),

                'pending_payment_timeouts' => \App\Models\Booking::query()
                    ->where('status', 'pending_payment')
                    ->where('created_at', '<', now()->subHour())
                    ->count(),

                'most_conflicted_vehicles' => \App\Models\Vehicle::query()
                    ->withCount(['bookings as cancelled_bookings_count' => function ($query): void {
                        $query->where('status', 'cancelled')
                            ->where('created_at', '>=', now()->subDays(30));
                    }])
                    ->having('cancelled_bookings_count', '>', 0)
                    ->orderByDesc('cancelled_bookings_count')
                    ->take(5)
                    ->get(['id', 'make', 'model', 'year'])
                    ->map(fn ($vehicle): array => [
                        'vehicle' => "{$vehicle->make} {$vehicle->model} ({$vehicle->year})",
                        'cancelled_count' => $vehicle->cancelled_bookings_count,
                    ]),

                'peak_booking_hours' => \App\Models\Booking::query()
                    ->selectRaw('HOUR(created_at) as hour, COUNT(*) as booking_count')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('hour')
                    ->orderByDesc('booking_count')
                    ->take(5)
                    ->get()
                    ->map(fn ($row): array => [
                        'hour' => $row->hour.':00',
                        'booking_count' => $row->booking_count,
                    ]),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get conflict statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
            ], 500);
        }
    }
}
