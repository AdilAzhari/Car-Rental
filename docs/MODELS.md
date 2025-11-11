# Database Models & Relationships

This document describes all Eloquent models in the Car Rental System, their relationships, attributes, and business logic.

## 📋 Table of Contents

- [Model Overview](#model-overview)
- [User Model](#user-model)
- [Vehicle Model](#vehicle-model)
- [Booking Model](#booking-model)
- [Payment Model](#payment-model)
- [Review Model](#review-model)
- [VehicleImage Model](#vehicleimage-model)
- [User Preference Models](#user-preference-models)
- [Log Model](#log-model)
- [Entity Relationship Diagram](#entity-relationship-diagram)

## Model Overview

| Model | Table | Purpose |
|-------|-------|---------|
| User | car_rental_users | System users (Admin, Owner, Renter) |
| Vehicle | car_rental_vehicles | Rental vehicles |
| Booking | car_rental_bookings | Vehicle reservations |
| Payment | car_rental_payments | Payment transactions |
| Review | car_rental_reviews | Vehicle reviews and ratings |
| VehicleImage | car_rental_vehicle_images | Vehicle photo gallery |
| UserFavorite | user_favorites | User's favorite vehicles |
| UserSearchPreference | user_search_preferences | User search preferences |
| UserRecentSearch | user_recent_searches | User search history |
| Log | car_rental_logs | Application logs |

## User Model

**Location**: `app/Models/User.php`
**Table**: `car_rental_users`

### Purpose
Manages system users with three roles: Admin, Owner (vehicle providers), and Renter (customers).

### Key Attributes

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'address',
    'role',                    // Enum: admin, owner, renter
    'is_verified',            // Boolean: email verification
    'email_verified_at',
    'status',                 // Enum: active, approved, pending, rejected
    'avatar',                 // Profile photo path
    'license_number',         // Driver's license
    'license_expiry_date',    // License expiration
    'id_document_path',       // ID document upload
    'license_document_path',  // License document upload
];

protected $casts = [
    'role' => UserRole::class,
    'status' => UserStatus::class,
    'is_verified' => 'boolean',
    'email_verified_at' => 'datetime',
    'license_expiry_date' => 'date',
];
```

### Relationships

#### One-to-Many

**Vehicles** (as owner)
```php
public function vehicles(): HasMany
{
    return $this->hasMany(Vehicle::class, 'owner_id');
}
```
- An owner can have multiple vehicles
- Used by owners to list their rental fleet

**Bookings** (as renter)
```php
public function bookings(): HasMany
{
    return $this->hasMany(Booking::class, 'renter_id');
}
```
- A renter can have multiple bookings
- Tracks booking history

**Reviews** (as author)
```php
public function reviews(): HasMany
{
    return $this->hasMany(Review::class, 'renter_id');
}
```
- A renter can write multiple reviews
- One review per booking

**Favorites**
```php
public function favorites(): HasMany
{
    return $this->hasMany(UserFavorite::class);
}
```

**Search Preferences**
```php
public function searchPreferences(): HasMany
{
    return $this->hasMany(UserSearchPreference::class);
}
```

**Recent Searches**
```php
public function recentSearches(): HasMany
{
    return $this->hasMany(UserRecentSearch::class);
}
```

### Key Methods

```php
// Check if user can access Filament panel
public function canAccessPanel(Panel $panel): bool
{
    return $this->role === UserRole::ADMIN ||
           $this->role === UserRole::OWNER;
}

// Check specific role
public function hasRole(string $role): bool
{
    return $this->role->value === $role;
}
```

### Soft Deletes
✅ Enabled - Users are soft deleted

### Activity Logging
✅ Enabled - All CRUD operations logged

---

## Vehicle Model

**Location**: `app/Models/Vehicle.php`
**Table**: `car_rental_vehicles`

### Purpose
Represents rental vehicles with complete specifications, availability tracking, and violation management.

### Key Attributes

```php
protected $fillable = [
    'owner_id',
    'make',                    // e.g., Toyota
    'model',                   // e.g., Camry
    'year',                    // e.g., 2023
    'license_plate',           // Unique identifier
    'color',
    'mileage',
    'daily_rate',             // Rental price per day
    'status',                 // Enum: available, rented, maintenance, unavailable
    'vin',                    // Vehicle Identification Number
    'fuel_type',              // Enum: petrol, diesel, electric, hybrid
    'transmission',           // Enum: manual, automatic
    'seats',
    'category',               // Enum: economy, compact, sedan, suv, luxury
    'features',               // JSON: air_conditioning, gps, etc.
    'description',
    'location',               // Current location
    'is_available',           // Quick availability flag

    // Insurance & Violations
    'insurance_policy_number',
    'insurance_expiry_date',
    'last_service_date',
    'next_service_date',
    'traffic_violations',     // JSON array of violations
    'total_violations_count',
    'total_fines_amount',
    'parking_violations',     // JSON array
    'total_parking_violations_count',
    'total_parking_fines_amount',
];

protected $casts = [
    'status' => VehicleStatus::class,
    'fuel_type' => VehicleFuelType::class,
    'transmission' => VehicleTransmission::class,
    'category' => VehicleCategory::class,
    'features' => 'array',
    'traffic_violations' => 'array',
    'parking_violations' => 'array',
    'daily_rate' => 'decimal:2',
    'is_available' => 'boolean',
    'insurance_expiry_date' => 'date',
    'last_service_date' => 'date',
    'next_service_date' => 'date',
];
```

### Relationships

#### Belongs To
**Owner**
```php
public function owner(): BelongsTo
{
    return $this->belongsTo(User::class, 'owner_id')
        ->withDefault([
            'name' => 'Unknown Owner',
            'email' => 'unknown@example.com',
        ]);
}
```

#### One-to-Many
**Images**
```php
public function images(): HasMany
{
    return $this->hasMany(VehicleImage::class);
}
```

**Bookings**
```php
public function bookings(): HasMany
{
    return $this->hasMany(Booking::class);
}
```

**Reviews**
```php
public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
```

**Logs**
```php
public function logs(): HasMany
{
    return $this->hasMany(Log::class);
}
```

### Scopes

```php
// Get available vehicles
Vehicle::available()->get();

// Get vehicles by owner
Vehicle::byOwner($ownerId)->get();

// Search vehicles
Vehicle::search($query)->get();
```

### Soft Deletes
✅ Enabled

### Activity Logging
✅ Enabled

---

## Booking Model

**Location**: `app/Models/Booking.php`
**Table**: `car_rental_bookings`

### Purpose
Manages vehicle reservations with automatic pricing calculation, conflict prevention, and status tracking.

### Key Attributes

```php
protected $fillable = [
    'renter_id',
    'vehicle_id',
    'start_date',
    'end_date',
    'pickup_location',
    'dropoff_location',
    'status',                 // Enum: pending, confirmed, ongoing, completed, cancelled
    'payment_status',         // Enum: unpaid, paid, refunded
    'total_amount',           // Auto-calculated
    'discount_amount',
    'tax_amount',
    'insurance_amount',
    'deposit_amount',
    'final_amount',           // After discounts/additions
    'special_requests',       // Customer notes
    'cancellation_reason',
    'cancelled_at',
];

protected $casts = [
    'status' => BookingStatus::class,
    'payment_status' => PaymentStatus::class,
    'start_date' => 'date',
    'end_date' => 'date',
    'cancelled_at' => 'datetime',
    'total_amount' => 'decimal:2',
    'final_amount' => 'decimal:2',
];
```

### Relationships

#### Belongs To
**Renter**
```php
public function renter(): BelongsTo
{
    return $this->belongsTo(User::class, 'renter_id');
}
```

**Vehicle**
```php
public function vehicle(): BelongsTo
{
    return $this->belongsTo(Vehicle::class);
}
```

#### One-to-Many
**Payments**
```php
public function payments(): HasMany
{
    return $this->hasMany(Payment::class);
}
```

#### One-to-One
**Review**
```php
public function review(): HasOne
{
    return $this->hasOne(Review::class);
}
```

### Accessors

```php
// Calculate rental duration
public function getDaysAttribute(): int
{
    return $this->start_date->diffInDays($this->end_date) + 1;
}
```

### Scopes

```php
// Get bookings by status
Booking::byStatus('confirmed')->get();

// Get active bookings
Booking::active()->get();

// Check vehicle availability
Booking::availableForDates($vehicleId, $startDate, $endDate)->exists();

// Get overlapping bookings
Booking::overlapping($vehicleId, $startDate, $endDate)->get();
```

### Database Constraints

```sql
-- Prevent double booking (unique constraint)
UNIQUE KEY booking_overlap_constraint (vehicle_id, start_date, end_date)
```

### Soft Deletes
✅ Enabled

### Activity Logging
✅ Enabled

---

## Payment Model

**Location**: `app/Models/Payment.php`
**Table**: `car_rental_payments`

### Purpose
Tracks payment transactions with multiple payment methods and status tracking.

### Key Attributes

```php
protected $fillable = [
    'booking_id',
    'amount',
    'payment_method',         // Enum: credit_card, bank_transfer, cash, online_banking
    'payment_status',         // Enum: unpaid, paid, refunded
    'transaction_id',         // External payment reference
    'paid_at',
    'refunded_at',
    'notes',
];

protected $casts = [
    'payment_method' => PaymentMethod::class,
    'payment_status' => PaymentStatus::class,
    'amount' => 'decimal:2',
    'paid_at' => 'datetime',
    'refunded_at' => 'datetime',
];
```

### Relationships

#### Belongs To
**Booking**
```php
public function booking(): BelongsTo
{
    return $this->belongsTo(Booking::class);
}
```

### Scopes

```php
// Get paid payments
Payment::paid()->get();

// Get payments by method
Payment::byMethod('credit_card')->get();
```

### Soft Deletes
✅ Enabled

### Activity Logging
✅ Enabled

---

## Review Model

**Location**: `app/Models/Review.php`
**Table**: `car_rental_reviews`

### Purpose
Customer reviews and ratings for vehicles after booking completion.

### Key Attributes

```php
protected $fillable = [
    'renter_id',
    'vehicle_id',
    'booking_id',
    'rating',                 // 1-5 stars
    'comment',
];

protected $casts = [
    'rating' => 'integer',
];
```

### Relationships

#### Belongs To
```php
public function renter(): BelongsTo;
public function vehicle(): BelongsTo;
public function booking(): BelongsTo;
```

### Validation Rules
- Rating: 1-5 (integer)
- One review per booking
- Only after booking completion

### Soft Deletes
✅ Enabled

### Activity Logging
✅ Enabled

---

## VehicleImage Model

**Location**: `app/Models/VehicleImage.php`
**Table**: `car_rental_vehicle_images`

### Purpose
Manages vehicle photo gallery with ordering and primary image designation.

### Key Attributes

```php
protected $fillable = [
    'vehicle_id',
    'image_path',
    'caption',
    'is_primary',             // Main display image
    'display_order',          // Sorting order
];

protected $casts = [
    'is_primary' => 'boolean',
    'display_order' => 'integer',
];
```

### Relationships

#### Belongs To
```php
public function vehicle(): BelongsTo
{
    return $this->belongsTo(Vehicle::class);
}
```

### Business Rules
- One primary image per vehicle
- Ordered by display_order
- Supports reordering via drag-and-drop

---

## User Preference Models

### UserFavorite

**Table**: `user_favorites`

Tracks user's favorite vehicles for quick access.

```php
protected $fillable = [
    'user_id',
    'vehicle_id',
];

// Relationships
public function user(): BelongsTo;
public function vehicle(): BelongsTo;
```

### UserSearchPreference

**Table**: `user_search_preferences`

Stores user's saved search filters.

```php
protected $fillable = [
    'user_id',
    'preference_name',
    'filters',                // JSON: category, price_range, etc.
];

protected $casts = [
    'filters' => 'array',
];
```

### UserRecentSearch

**Table**: `user_recent_searches`

Tracks user's recent search queries.

```php
protected $fillable = [
    'user_id',
    'search_query',
    'filters',
    'results_count',
];

protected $casts = [
    'filters' => 'array',
];
```

---

## Log Model

**Location**: `app/Models/Log.php`
**Table**: `car_rental_logs`

### Purpose
Application-specific logging beyond activity logs.

```php
protected $fillable = [
    'user_id',
    'vehicle_id',
    'booking_id',
    'log_type',              // e.g., error, warning, info
    'message',
    'context',               // JSON additional data
];

protected $casts = [
    'context' => 'array',
];
```

---

## Entity Relationship Diagram

```
┌─────────────┐
│    User     │
│  (Admin/    │
│  Owner/     │
│  Renter)    │
└──────┬──────┘
       │
       ├──────────────┐ owns (owner_id)
       │              │
       │         ┌────▼─────┐
       │         │ Vehicle  │──┐
       │         └────┬─────┘  │
       │              │        │
       │              │        │ has many
       │              │        │
       │         ┌────▼──────────────┐
       │         │  VehicleImage     │
       │         └───────────────────┘
       │
       │
       ├──────────────┐ rents (renter_id)
       │              │
       │         ┌────▼─────┐
       │         │ Booking  │──┐
       │         └────┬─────┘  │
       │              │        │
       │              │        │ has many
       │              │        │
       │         ┌────▼────────┴─────┐
       │         │     Payment       │
       │         └───────────────────┘
       │              │
       │              │ has one
       │              │
       │         ┌────▼────────┬──────┐
       │         │   Review    │      │
       │         └─────────────┘      │
       │                              │
       │                              │
       ├──────────────┐               │
       │              │               │
       │         ┌────▼──────────┐    │
       │         │ UserFavorite  │    │
       │         └───────────────┘    │
       │                              │
       │         ┌──────────────────┐ │
       │         │UserSearchPref    │ │
       │         └──────────────────┘ │
       │                              │
       │         ┌──────────────────┐ │
       │         │UserRecentSearch  │ │
       │         └──────────────────┘ │
       │                              │
       └──────────────────────────────┘
```

### Cardinality Summary

- **User → Vehicle**: 1:N (owner)
- **User → Booking**: 1:N (renter)
- **User → Review**: 1:N (author)
- **Vehicle → Booking**: 1:N
- **Vehicle → VehicleImage**: 1:N
- **Vehicle → Review**: 1:N
- **Booking → Payment**: 1:N
- **Booking → Review**: 1:1
- **User → UserFavorite**: 1:N
- **User → UserSearchPreference**: 1:N
- **User → UserRecentSearch**: 1:N

---

## Database Indexes

### Performance Indexes

```sql
-- Users
INDEX idx_users_email (email)
INDEX idx_users_role (role)
INDEX idx_users_status (status)

-- Vehicles
INDEX idx_vehicles_owner_id (owner_id)
INDEX idx_vehicles_status (status)
INDEX idx_vehicles_daily_rate (daily_rate)
INDEX idx_vehicles_category (category)

-- Bookings
INDEX idx_bookings_renter_id (renter_id)
INDEX idx_bookings_vehicle_id (vehicle_id)
INDEX idx_bookings_status (status)
INDEX idx_bookings_dates (start_date, end_date)
UNIQUE idx_booking_overlap (vehicle_id, start_date, end_date)

-- Payments
INDEX idx_payments_booking_id (booking_id)
INDEX idx_payments_status (payment_status)

-- Reviews
INDEX idx_reviews_vehicle_id (vehicle_id)
INDEX idx_reviews_renter_id (renter_id)
INDEX idx_reviews_rating (rating)
```

---

## Model Events & Observers

### User Observer
- **Creating**: Hash password, set default status
- **Updated**: Log role changes
- **Deleted**: Soft delete related records

### Vehicle Observer
- **Creating**: Set is_available flag
- **Updated**: Update availability status
- **Deleted**: Cancel active bookings

### Booking Observer
- **Creating**: Validate dates, check availability
- **Created**: Update vehicle status, send notification
- **Updated**: Track status changes, send notifications
- **Deleted**: Refund payments if applicable

### Payment Observer
- **Created**: Update booking payment status
- **Updated**: Handle refunds

### Review Observer
- **Created**: Update vehicle average rating
- **Updated**: Recalculate vehicle rating
- **Deleted**: Recalculate vehicle rating

---

**Last Updated**: 2025-11-11
