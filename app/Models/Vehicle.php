<?php

namespace App\Models;

use App\Enums\VehicleFuelType;
use App\Enums\VehicleStatus;
use App\Enums\VehicleTransmission;
use App\Observers\VehicleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([VehicleObserver::class])]
class Vehicle extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'car_rental_vehicles';

    protected $fillable = [
        'owner_id',
        'make',
        'model',
        'year',
        'color',
        'plate_number',
        'fuel_type',
        'transmission',
        'seats',
        'daily_rate',
        'description',
        'status',
        'is_available',
        'location',
        'mileage',
        'insurance_expiry',
        'insurance_fine_amount',
        'insurance_fine_paid',
        'insurance_fine_paid_at',
        'insurance_fine_payment_method',
        'insurance_fine_transaction_id',
        'oil_type',
        'last_oil_change',
        'policy',
        'category',
        'doors',
        'engine_size',
        'pickup_location',
        'insurance_included',
        'featured_image',
        'gallery_images',
        'documents',
        'features',
        'terms_and_conditions',
        'traffic_violations',
        'violations_last_checked',
        'total_violations_count',
        'total_fines_amount',
        'has_pending_violations',
        'parking_violations',
        'parking_violations_last_checked',
        'total_parking_violations_count',
        'total_parking_fines_amount',
        'has_pending_parking_violations',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'year' => 'integer',
            'seats' => 'integer',
            'doors' => 'integer',
            'mileage' => 'integer',
            'engine_size' => 'decimal:1',
            'last_oil_change' => 'date',
            'insurance_expiry' => 'date',
            'insurance_fine_amount' => 'decimal:2',
            'insurance_fine_paid' => 'boolean',
            'insurance_fine_paid_at' => 'datetime',
            'fuel_type' => VehicleFuelType::class,
            'transmission' => VehicleTransmission::class,
            'status' => VehicleStatus::class,
            'is_available' => 'boolean',
            'insurance_included' => 'boolean',
            'gallery_images' => 'array',
            'documents' => 'array',
            'features' => 'array',
            'traffic_violations' => 'array',
            'violations_last_checked' => 'datetime',
            'total_violations_count' => 'integer',
            'total_fines_amount' => 'decimal:2',
            'has_pending_violations' => 'boolean',
            'parking_violations' => 'array',
            'parking_violations_last_checked' => 'datetime',
            'total_parking_violations_count' => 'integer',
            'total_parking_fines_amount' => 'decimal:2',
            'has_pending_parking_violations' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->withDefault([
            'name' => 'Unknown Owner',
            'email' => 'unknown@example.com',
        ]);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // === ELOQUENT SCOPES FOR COMPLEX QUERIES ===

    /**
     * Scope vehicles for specific user role with dynamic permissions
     */
    #[Scope]
    public function forRole($query, $role, $userId = null)
    {
        $userId ??= auth()->id();

        return match ($role) {
            'admin' => $query, // Admins see everything
            'owner' => $query->where('owner_id', $userId),
            'renter' => $query->where('status', 'published')->where('is_available', true),
            default => $query->whereRaw('1 = 0') // No access for unknown roles
        };
    }

    /**
     * Scope for available vehicles with smart filtering
     */
    #[Scope]
    public function availableForRent($query, $startDate = null, $endDate = null)
    {
        $query->where('status', 'published')
            ->where('is_available', true)
            ->where('insurance_expiry', '>', now());

        // Check for booking conflicts if dates provided
        if ($startDate && $endDate) {
            $query->whereDoesntHave('bookings', function ($bookingQuery) use ($startDate, $endDate): void {
                $bookingQuery->where('status', '!=', 'cancelled')
                    ->where(function ($dateQuery) use ($startDate, $endDate): void {
                        $dateQuery->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($overlapQuery) use ($startDate, $endDate): void {
                                $overlapQuery->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    });
            });
        }

        return $query;
    }

    /**
     * Scope for advanced search with multiple criteria
     */
    #[Scope]
    public function advancedSearch($query, array $filters)
    {
        return $query->when($filters['make'] ?? null, fn ($q, $make) => $q->where('make', 'like', "%$make%"))
            ->when($filters['model'] ?? null, fn ($q, $model) => $q->where('model', 'like', "%{$model}%"))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['min_price'] ?? null, fn ($q, $minPrice) => $q->where('daily_rate', '>=', $minPrice))
            ->when($filters['max_price'] ?? null, fn ($q, $maxPrice) => $q->where('daily_rate', '<=', $maxPrice))
            ->when($filters['transmission'] ?? null, fn ($q, $transmission) => $q->where('transmission', $transmission))
            ->when($filters['fuel_type'] ?? null, fn ($q, $fuelType) => $q->where('fuel_type', $fuelType))
            ->when($filters['min_seats'] ?? null, fn ($q, $minSeats) => $q->where('seats', '>=', $minSeats))
            ->when($filters['max_year'] ?? null, fn ($q, $maxYear) => $q->where('year', '<=', $maxYear))
            ->when($filters['min_year'] ?? null, fn ($q, $minYear) => $q->where('year', '>=', $minYear))
            ->when($filters['location'] ?? null, fn ($q, $location) => $q->where('location', 'like', "%{$location}%"));
    }

    /**
     * Scope for popular vehicles based on bookings and reviews
     */
    #[Scope]
    public function popular($query, $limit = 10)
    {
        return $query->withCount(['bookings', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->groupBy('car_rental_vehicles.id')
            ->having('bookings_count', '>', 0)
            ->orderByRaw('(bookings_count * 0.7 + COALESCE(reviews_avg_rating, 0) * 0.3) DESC')
            ->limit($limit);
    }

    /**
     * Scope for vehicles with maintenance issues or violations
     */
    #[Scope]
    public function requiringAttention($query)
    {
        return $query->where(function ($mainQuery): void {
            $mainQuery->where('status', 'maintenance')
                ->orWhere('insurance_expiry', '<=', now()->addDays(30))
                ->orWhere('has_pending_violations', true)
                ->orWhereJsonLength('traffic_violations', '>', 0);
        });
    }

    /**
     * Scope for revenue analysis by owner
     */
    #[Scope]
    public function revenueAnalysis($query, $startDate = null, $endDate = null)
    {
        $startDate ??= now()->subYear();
        $endDate ??= now();

        return $query->withSum(['bookings' => function ($bookingQuery) use ($startDate, $endDate): void {
            $bookingQuery->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate]);
        }], 'total_amount')
            ->withCount(['bookings' => function ($bookingQuery) use ($startDate, $endDate): void {
                $bookingQuery->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderBy('bookings_sum_total_amount', 'desc');
    }

    /**
     * Scope for nearby vehicles using location matching
     */
    #[Scope]
    public function nearby($query, $location, $radius = 50)
    {
        // Simple text-based proximity (in real app, use spatial queries)
        return $query->where('location', 'like', "%{$location}%")
            ->orWhere('pickup_location', 'like', "%{$location}%");
    }

    /**
     * Scope for price range filtering
     */
    #[Scope]
    public function priceRange($query, $minPrice = null, $maxPrice = null)
    {
        return $query->when($minPrice, fn ($q) => $q->where('daily_rate', '>=', $minPrice))
            ->when($maxPrice, fn ($q) => $q->where('daily_rate', '<=', $maxPrice));
    }

    /**
     * Scope for vehicles with images
     */
    #[Scope]
    public function withImages($query)
    {
        return $query->whereNotNull('featured_image')
            ->orWhereJsonLength('gallery_images', '>', 0)
            ->orWhereHas('images');
    }

    /**
     * Scope for premium vehicles (high-rated with luxury features)
     */
    #[Scope]
    public function premium($query)
    {
        return $query->where('category', 'luxury')
            ->where('daily_rate', '>', 100)
            ->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>=', 4.0);
    }

    /**
     * Scope for eco-friendly vehicles
     */
    #[Scope]
    public function ecoFriendly($query)
    {
        return $query->whereIn('fuel_type', ['electric', 'hybrid']);
    }

    /**
     * Scope for family vehicles (suitable for families)
     */
    #[Scope]
    public function familyFriendly($query)
    {
        return $query->where('seats', '>=', 5)
            ->whereIn('category', ['suv', 'minivan', 'midsize']);
    }

    /**
     * Scope for recently added vehicles
     */
    #[Scope]
    public function recent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for vehicles with high ratings
     */
    #[Scope]
    public function highlyRated($query, $minRating = 4.0)
    {
        return $query->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>=', $minRating);
    }

    /**
     * Scope for vehicles by owner
     */
    #[Scope]
    public function byOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope for vehicles with active bookings
     */
    #[Scope]
    public function withActiveBookings($query)
    {
        return $query->whereHas('bookings', function ($bookingQuery): void {
            $bookingQuery->where('status', 'confirmed')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now());
        });
    }

    /**
     * Scope for most booked vehicles
     */
    #[Scope]
    public function mostBooked($query, $limit = 10)
    {
        return $query->withCount(['bookings' => function ($bookingQuery): void {
            $bookingQuery->where('status', 'completed');
        }])
            ->orderBy('bookings_count', 'desc')
            ->limit($limit);
    }

    /**
     * Get the featured image URL with proper fallback logic
     */
    public function getFeaturedImageUrl(): ?string
    {
        // Priority 1: Primary image from images relationship
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $primaryImage = $this->images->where('is_primary', true)->first();
            if ($primaryImage) {
                return Storage::url($primaryImage->image_path);
            }

            // Fallback to first image if no primary
            $firstImage = $this->images->first();
            if ($firstImage) {
                return Storage::url($firstImage->image_path);
            }
        }

        // Priority 2: featured_image field
        if ($this->featured_image) {
            return Storage::url($this->featured_image);
        }

        // Priority 3: First image from gallery_images array
        if ($this->gallery_images && is_array($this->gallery_images) && count($this->gallery_images) > 0) {
            return Storage::url($this->gallery_images[0]);
        }

        // No image available
        return null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vehicle')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Vehicle was added to the system',
                'updated' => 'Vehicle details were updated',
                'deleted' => 'Vehicle was removed from the system',
                default => "Vehicle was {$eventName}"
            });
    }
}
