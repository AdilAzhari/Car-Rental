<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model implements Eventable
{
    use HasFactory, SoftDeletes;

    protected $table = 'car_rental_bookings';

    protected $fillable = [
        'renter_id',
        'vehicle_id',
        'start_date',
        'end_date',
        'days',
        'daily_rate',
        'subtotal',
        'insurance_fee',
        'tax_amount',
        'total_amount',
        'status',
        'pickup_location',
        'dropoff_location',
        'special_requests',
        'deposit_amount',
        'commission_amount',
        'payment_status',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'daily_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function getDaysAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    // === ELOQUENT SCOPES FOR COMPLEX BOOKING QUERIES ===

    /**
     * Scope bookings by status with role-based access
     */
    #[Scope]
    public function byStatus($query, $status, $userRole = null)
    {
        $query->where('status', $status);

        // Role-based filtering
        if ($userRole === 'renter') {
            $query->where('renter_id', auth()->id());
        } elseif ($userRole === 'owner') {
            $query->whereHas('vehicle', fn ($q) => $q->where('owner_id', auth()->id()));
        }

        return $query;
    }

    /**
     * Scope for active bookings (ongoing or confirmed)
     */
    #[Scope]
    public function active($query)
    {
        return $query->whereIn('status', ['confirmed', 'ongoing'])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope for upcoming bookings
     */
    #[Scope]
    public function upcoming($query, $days = 30)
    {
        return $query->where('status', 'confirmed')
            ->where('start_date', '>', now())
            ->where('start_date', '<=', now()->addDays($days))
            ->orderBy('start_date');
    }

    /**
     * Scope for overdue returns
     */
    #[Scope]
    public function overdue($query)
    {
        return $query->where('status', 'ongoing')
            ->where('end_date', '<', now())
            ->with(['renter', 'vehicle']);
    }

    /**
     * Scope for revenue analysis with date ranges
     */
    #[Scope]
    public function revenueInPeriod($query, $startDate = null, $endDate = null, $ownerId = null)
    {
        $startDate ??= now()->startOfMonth();
        $endDate ??= now()->endOfMonth();

        $query->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($ownerId) {
            $query->whereHas('vehicle', fn ($q) => $q->where('owner_id', $ownerId));
        }

        return $query->with(['vehicle', 'payments'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope for bookings requiring attention (payments pending, reviews missing, etc.)
     */
    #[Scope]
    public function requiringAttention($query)
    {
        return $query->where(function ($mainQuery): void {
            $mainQuery->where(function ($paymentQuery): void {
                // Bookings with pending payments
                $paymentQuery->where('payment_status', 'pending')
                    ->where('created_at', '<', now()->subHours(24));
            })->orWhere(function ($completedQuery): void {
                // Completed bookings without reviews (older than 7 days)
                $completedQuery->where('status', 'completed')
                    ->where('end_date', '<', now()->subDays(7))
                    ->doesntHave('review');
            })->orWhere(function ($overdueQuery): void {
                // Overdue ongoing bookings
                $overdueQuery->where('status', 'ongoing')
                    ->where('end_date', '<', now());
            });
        });
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $color = match ($this->status) {
            BookingStatus::PENDING => '#fbbf24', // yellow
            BookingStatus::CONFIRMED => '#10b981', // green
            BookingStatus::ONGOING => '#3b82f6', // blue
            BookingStatus::CANCELLED => '#ef4444', // red
            default => '#6b7280'
        };

        // Handle null relationships gracefully
        $vehicleInfo = $this->vehicle
            ? "{$this->vehicle->make} {$this->vehicle->model}"
            : 'Vehicle Not Available';

        $renterName = $this->renter?->name ?? 'Renter Not Available';

        $title = "$vehicleInfo - $renterName";

        return CalendarEvent::make($this)
            ->title($title)
            ->start($this->start_date)
            ->end($this->end_date)
            ->backgroundColor($color)
            ->textColor('#ffffff')
            ->extendedProp('status', $this->status->label())
            ->extendedProp('vehicle', $vehicleInfo)
            ->extendedProp('renter', $renterName)
            ->extendedProp('total_amount', $this->total_amount)
            ->action('view');
    }
}
