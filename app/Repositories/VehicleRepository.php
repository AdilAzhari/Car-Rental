<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleRepository
{
    public function searchWithFilters(Request $request): LengthAwarePaginator
    {
        $query = $this->baseSearchQuery()
            ->availableForRent(
                $request->start_date,
                $request->end_date
            );

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $perPage = min($request->get('per_page', 12), 50);

        return $query->paginate($perPage);
    }

    public function findWithDetails(int $vehicleId): Vehicle
    {
        return Vehicle::with([
            'owner',
            'images',
            'reviews.renter',
            'bookings' => function ($query): void {
                $query->where('status', '!=', 'cancelled')
                    ->where('end_date', '>=', now())
                    ->select(['vehicle_id', 'start_date', 'end_date', 'status']);
            },
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->findOrFail($vehicleId);
    }

    public function checkAvailability(int $vehicleId, string $startDate, string $endDate): bool
    {
        return ! Vehicle::query()->findOrFail($vehicleId)
            ->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($overlapQuery) use ($startDate, $endDate): void {
                        $overlapQuery->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Check vehicle availability with pessimistic locking to prevent race conditions
     */
    public function checkAvailabilityWithLock(int $vehicleId, string $startDate, string $endDate): bool
    {
        // First, lock the vehicle record to prevent concurrent modifications
        $vehicle = Vehicle::query()
            ->lockForUpdate()
            ->findOrFail($vehicleId);

        // Check if vehicle is available for booking
        if (! $vehicle->is_available || $vehicle->status !== \App\Enums\VehicleStatus::PUBLISHED) {
            return false;
        }

        // Check for overlapping bookings with pessimistic locking
        $hasConflictingBookings = $vehicle->bookings()
            ->lockForUpdate()
            ->whereIn('status', ['confirmed', 'ongoing', 'pending'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->where(function ($dateQuery) use ($startDate): void {
                    // New booking starts during an existing booking
                    $dateQuery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>', $startDate);
                })->orWhere(function ($dateQuery) use ($endDate): void {
                    // New booking ends during an existing booking
                    $dateQuery->where('start_date', '<', $endDate)
                        ->where('end_date', '>=', $endDate);
                })->orWhere(function ($dateQuery) use ($startDate, $endDate): void {
                    // New booking completely encompasses an existing booking
                    $dateQuery->where('start_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                })->orWhere(function ($dateQuery) use ($startDate, $endDate): void {
                    // Existing booking completely encompasses new booking
                    $dateQuery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
            })
            ->exists();

        return ! $hasConflictingBookings;
    }

    public function getOwnerVehicles(int $ownerId): Collection
    {
        return Vehicle::with(['images', 'bookings' => function ($query): void {
            $query->where('status', '!=', 'cancelled')
                ->latest()
                ->take(5);
        }])
            ->withCount(['bookings', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('owner_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVehicleStatistics(int $vehicleId): array
    {
        $vehicle = Vehicle::query()->withCount([
            'bookings',
            'bookings as completed_bookings_count' => function ($query): void {
                $query->where('status', 'completed');
            },
            'reviews',
        ])
            ->withAvg('reviews', 'rating')
            ->withSum('bookings as total_revenue', 'total_amount')
            ->findOrFail($vehicleId);

        return [
            'total_bookings' => $vehicle->bookings_count,
            'completed_bookings' => $vehicle->completed_bookings_count,
            'total_reviews' => $vehicle->reviews_count,
            'average_rating' => $vehicle->reviews_avg_rating ? round($vehicle->reviews_avg_rating, 2) : null,
            'total_revenue' => $vehicle->total_revenue ?: 0,
            'occupancy_rate' => $this->calculateOccupancyRate($vehicle),
        ];
    }

    public function getVehiclesNeedingAttention(int $ownerId): Collection
    {
        return Vehicle::query()->where('owner_id', $ownerId)
            ->where(function ($query): void {
                $query->where('insurance_expiry', '<=', now()->addDays(30))
                    ->orWhere('is_available', false)
                    ->orWhereHas('reviews', function ($reviewQuery): void {
                        $reviewQuery->where('rating', '<=', 2)
                            ->where('created_at', '>=', now()->subDays(30));
                    });
            })
            ->with(['images', 'reviews' => function ($query): void {
                $query->where('rating', '<=', 2)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->latest()
                    ->take(3);
            }])
            ->get();
    }

    public function getPopularVehicles(int $limit = 10): Collection
    {
        return Vehicle::with(['owner', 'images'])
            ->withCount(['bookings', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('is_available', true)
            ->where('status', 'published')
            ->orderByDesc('bookings_count')
            ->orderByDesc('reviews_avg_rating')
            ->take($limit)
            ->get();
    }

    public function getFeaturedVehicles(int $limit = 6): Collection
    {
        return Vehicle::with(['owner', 'images'])
            ->withAvg('reviews', 'rating')
            ->where('is_available', true)
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    private function baseSearchQuery(): Builder
    {
        return Vehicle::with(['owner', 'images', 'reviews']);
    }

    private function applyFilters(Builder $builder, Request $request): void
    {
        if ($request->filled('location')) {
            $builder->nearby($request->location);
        }

        if ($request->filled(['price_min', 'price_max'])) {
            $builder->priceRange($request->price_min, $request->price_max);
        }

        if ($request->filled('transmission')) {
            $builder->where('transmission', $request->transmission);
        }

        if ($request->filled('fuel_type')) {
            $builder->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('category')) {
            $builder->where('category', $request->category);
        }

        if ($request->filled('seats')) {
            $builder->where('seats', '>=', $request->seats);
        }
    }

    private function applySorting(Builder $builder, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        match ($sortBy) {
            'price_low' => $builder->orderBy('daily_rate'),
            'price_high' => $builder->orderBy('daily_rate', 'desc'),
            'rating' => $builder->orderByRaw('(SELECT AVG(rating) FROM reviews WHERE vehicle_id = car_rental_vehicles.id) DESC NULLS LAST'),
            'popular' => $builder->popular(),
            default => $builder->orderBy($sortBy, $sortOrder)
        };
    }

    private function calculateOccupancyRate(Vehicle $vehicle): float
    {
        $daysInYear = 365;
        $bookings = $vehicle->bookings()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->get();

        $bookedDays = $bookings->sum(fn ($booking): float => \Carbon\Carbon::parse($booking->start_date)
            ->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1);

        return $bookedDays > 0 ? round(($bookedDays / $daysInYear) * 100, 2) : 0;
    }
}
