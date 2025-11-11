# Enumerations & Data Transfer Objects

This document describes all Enum classes and DTOs used throughout the Car Rental System for type safety and data structure.

## 📋 Table of Contents

- [Enumerations](#enumerations)
  - [UserRole](#userrole)
  - [UserStatus](#userstatus)
  - [VehicleStatus](#vehiclestatus)
  - [VehicleCategory](#vehiclecategory)
  - [VehicleFuelType](#vehiclefueltype)
  - [VehicleTransmission](#vehicletransmission)
  - [BookingStatus](#bookingstatus)
  - [PaymentStatus](#paymentstatus)
  - [PaymentMethod](#paymentmethod)
- [Data Transfer Objects](#data-transfer-objects)

## Enumerations

All enums are backed enums implementing standard interfaces for consistency and type safety.

### UserRole

**Location**: `app/Enums/UserRole.php`

Defines user access levels in the system.

```php
enum UserRole: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case RENTER = 'renter';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::OWNER => 'Vehicle Owner',
            self::RENTER => 'Customer',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full system access and management',
            self::OWNER => 'Can list and manage vehicles',
            self::RENTER => 'Can rent vehicles and make bookings',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
```

#### Usage

```php
// Creating a user
$user = User::create([
    'name' => 'John Doe',
    'role' => UserRole::OWNER,
]);

// Checking role
if ($user->role === UserRole::ADMIN) {
    // Admin-only logic
}

// In Filament select
Select::make('role')
    ->options(UserRole::options())
    ->required();

// Get label for display
echo $user->role->label(); // "Vehicle Owner"
```

#### Permissions by Role

| Permission | Admin | Owner | Renter |
|------------|-------|-------|--------|
| Manage All Users | ✅ | ❌ | ❌ |
| Manage Own Vehicles | ✅ | ✅ | ❌ |
| View All Bookings | ✅ | ⚠️ (Own vehicles only) | ⚠️ (Own bookings only) |
| Access Admin Panel | ✅ | ✅ | ❌ |
| Make Bookings | ✅ | ✅ | ✅ |
| Leave Reviews | ❌ | ❌ | ✅ |

---

### UserStatus

**Location**: `app/Enums/UserStatus.php`

Tracks user account status for approval workflows.

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::APPROVED => 'Approved',
            self::PENDING => 'Pending Approval',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::APPROVED => 'info',
            self::PENDING => 'warning',
            self::REJECTED => 'danger',
        };
    }
}
```

#### Status Workflow

```
Registration
     ↓
  PENDING ──→ (Admin Review) ──→ APPROVED ──→ ACTIVE
     │                                ↓
     └──────────────────────────→ REJECTED
```

#### Usage

```php
// Check if user can login
if ($user->status === UserStatus::ACTIVE) {
    // Allow login
}

// Update status
$user->update(['status' => UserStatus::APPROVED]);

// Display badge
BadgeColumn::make('status')
    ->formatStateUsing(fn($state) => $state->label())
    ->color(fn($state) => $state->color());
```

---

### VehicleStatus

**Location**: `app/Enums/VehicleStatus.php`

Represents vehicle availability and current state.

```php
enum VehicleStatus: string
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case MAINTENANCE = 'maintenance';
    case UNAVAILABLE = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available for Rent',
            self::RENTED => 'Currently Rented',
            self::MAINTENANCE => 'Under Maintenance',
            self::UNAVAILABLE => 'Not Available',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::RENTED => 'info',
            self::MAINTENANCE => 'warning',
            self::UNAVAILABLE => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AVAILABLE => 'heroicon-o-check-circle',
            self::RENTED => 'heroicon-o-clock',
            self::MAINTENANCE => 'heroicon-o-wrench',
            self::UNAVAILABLE => 'heroicon-o-x-circle',
        };
    }

    public function canBeBooked(): bool
    {
        return $this === self::AVAILABLE;
    }
}
```

#### Usage

```php
// Check availability
if ($vehicle->status->canBeBooked()) {
    // Allow booking
}

// Filter available vehicles
$vehicles = Vehicle::where('status', VehicleStatus::AVAILABLE)->get();

// Update status when booking
$vehicle->update(['status' => VehicleStatus::RENTED]);
```

---

### VehicleCategory

**Location**: `app/Enums/VehicleCategory.php`

Vehicle classification for filtering and pricing tiers.

```php
enum VehicleCategory: string
{
    case ECONOMY = 'economy';
    case COMPACT = 'compact';
    case SEDAN = 'sedan';
    case SUV = 'suv';
    case LUXURY = 'luxury';
    case VAN = 'van';
    case TRUCK = 'truck';
    case SPORT = 'sport';

    public function label(): string
    {
        return match ($this) {
            self::ECONOMY => 'Economy',
            self::COMPACT => 'Compact',
            self::SEDAN => 'Sedan',
            self::SUV => 'SUV',
            self::LUXURY => 'Luxury',
            self::VAN => 'Van',
            self::TRUCK => 'Truck',
            self::SPORT => 'Sport',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ECONOMY => 'Budget-friendly, fuel-efficient cars',
            self::COMPACT => 'Small, easy to park, good for city driving',
            self::SEDAN => 'Comfortable 4-door vehicles',
            self::SUV => 'Spacious vehicles with extra cargo space',
            self::LUXURY => 'Premium vehicles with advanced features',
            self::VAN => 'Large passenger or cargo capacity',
            self::TRUCK => 'Heavy-duty vehicles for hauling',
            self::SPORT => 'High-performance vehicles',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ECONOMY => '🚗',
            self::COMPACT => '🚙',
            self::SEDAN => '🚘',
            self::SUV => '🚐',
            self::LUXURY => '🏎️',
            self::VAN => '🚐',
            self::TRUCK => '🚚',
            self::SPORT => '🏁',
        };
    }

    // Typical daily rate range (for reference)
    public function priceRange(): array
    {
        return match ($this) {
            self::ECONOMY => [50, 80],
            self::COMPACT => [60, 100],
            self::SEDAN => [80, 130],
            self::SUV => [120, 200],
            self::LUXURY => [250, 500],
            self::VAN => [100, 180],
            self::TRUCK => [90, 150],
            self::SPORT => [200, 400],
        };
    }
}
```

#### Usage

```php
// Create vehicle
$vehicle = Vehicle::create([
    'category' => VehicleCategory::SEDAN,
    'daily_rate' => 100.00,
]);

// Filter by category
$suvs = Vehicle::where('category', VehicleCategory::SUV)->get();

// Display with icon
echo $vehicle->category->icon() . ' ' . $vehicle->category->label();
```

---

### VehicleFuelType

**Location**: `app/Enums/VehicleFuelType.php`

```php
enum VehicleFuelType: string
{
    case PETROL = 'petrol';
    case DIESEL = 'diesel';
    case ELECTRIC = 'electric';
    case HYBRID = 'hybrid';
    case PLUGIN_HYBRID = 'plugin_hybrid';

    public function label(): string
    {
        return match ($this) {
            self::PETROL => 'Petrol',
            self::DIESEL => 'Diesel',
            self::ELECTRIC => 'Electric',
            self::HYBRID => 'Hybrid',
            self::PLUGIN_HYBRID => 'Plug-in Hybrid',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PETROL => '⛽',
            self::DIESEL => '⛽',
            self::ELECTRIC => '🔌',
            self::HYBRID => '🔋',
            self::PLUGIN_HYBRID => '🔌🔋',
        };
    }

    public function isElectrified(): bool
    {
        return in_array($this, [
            self::ELECTRIC,
            self::HYBRID,
            self::PLUGIN_HYBRID,
        ]);
    }
}
```

---

### VehicleTransmission

**Location**: `app/Enums/VehicleTransmission.php`

```php
enum VehicleTransmission: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
    case SEMI_AUTOMATIC = 'semi_automatic';
    case CVT = 'cvt';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Automatic',
            self::SEMI_AUTOMATIC => 'Semi-Automatic',
            self::CVT => 'CVT (Continuously Variable)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual gear shifting',
            self::AUTOMATIC => 'Automatic gear shifting',
            self::SEMI_AUTOMATIC => 'Automated manual transmission',
            self::CVT => 'Smooth, stepless gear ratios',
        };
    }
}
```

---

### BookingStatus

**Location**: `app/Enums/BookingStatus.php`

Tracks booking lifecycle from creation to completion.

```php
enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Confirmation',
            self::CONFIRMED => 'Confirmed',
            self::ONGOING => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
            self::ONGOING => 'info',
            self::COMPLETED => 'primary',
            self::CANCELLED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::ONGOING => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::CONFIRMED,
        ]);
    }

    public function canBeReviewed(): bool
    {
        return $this === self::COMPLETED;
    }
}
```

#### Booking Status Flow

```
Creation
   ↓
PENDING ──→ CONFIRMED ──→ ONGOING ──→ COMPLETED
   │            │
   ↓            ↓
CANCELLED   CANCELLED
```

#### Usage

```php
// Check if booking can be cancelled
if ($booking->status->canBeCancelled()) {
    $booking->update([
        'status' => BookingStatus::CANCELLED,
        'cancelled_at' => now(),
    ]);
}

// Check if review can be left
if ($booking->status->canBeReviewed()) {
    // Show review form
}

// Automatic status updates
// - PENDING → CONFIRMED: When payment received or admin approves
// - CONFIRMED → ONGOING: On start_date
// - ONGOING → COMPLETED: On end_date
```

---

### PaymentStatus

**Location**: `app/Enums/PaymentStatus.php`

```php
enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'secondary',
            self::PARTIALLY_REFUNDED => 'info',
            self::FAILED => 'danger',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function canBeRefunded(): bool
    {
        return in_array($this, [
            self::PAID,
            self::PARTIALLY_REFUNDED,
        ]);
    }
}
```

---

### PaymentMethod

**Location**: `app/Enums/PaymentMethod.php`

```php
enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case ONLINE_BANKING = 'online_banking';
    case E_WALLET = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
            self::ONLINE_BANKING => 'Online Banking',
            self::E_WALLET => 'E-Wallet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::DEBIT_CARD => 'heroicon-o-credit-card',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CASH => 'heroicon-o-banknotes',
            self::ONLINE_BANKING => 'heroicon-o-computer-desktop',
            self::E_WALLET => 'heroicon-o-device-phone-mobile',
        };
    }

    public function requiresVerification(): bool
    {
        return in_array($this, [
            self::BANK_TRANSFER,
            self::CASH,
        ]);
    }

    public function isInstant(): bool
    {
        return in_array($this, [
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::ONLINE_BANKING,
            self::E_WALLET,
        ]);
    }
}
```

---

## Data Transfer Objects

DTOs provide structured data transfer between layers of the application.

### Purpose
- Type-safe data structures
- Validation at boundaries
- Immutable data transfer
- API response formatting
- Decoupling from Eloquent models

### Common DTO Structure

```php
namespace App\DTOs;

readonly class VehicleDTO
{
    public function __construct(
        public int $id,
        public string $make,
        public string $model,
        public int $year,
        public string $licensePlate,
        public float $dailyRate,
        public VehicleStatus $status,
        public VehicleCategory $category,
        public ?array $features = null,
        public ?array $images = null,
        public ?float $averageRating = null,
        public ?int $totalReviews = null,
    ) {}

    public static function fromModel(Vehicle $vehicle): self
    {
        return new self(
            id: $vehicle->id,
            make: $vehicle->make,
            model: $vehicle->model,
            year: $vehicle->year,
            licensePlate: $vehicle->license_plate,
            dailyRate: $vehicle->daily_rate,
            status: $vehicle->status,
            category: $vehicle->category,
            features: $vehicle->features,
            images: $vehicle->images->pluck('image_path')->toArray(),
            averageRating: $vehicle->reviews()->avg('rating'),
            totalReviews: $vehicle->reviews()->count(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'license_plate' => $this->licensePlate,
            'daily_rate' => $this->dailyRate,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'features' => $this->features,
            'images' => $this->images,
            'average_rating' => $this->averageRating,
            'total_reviews' => $this->totalReviews,
        ];
    }
}
```

### Usage in Controllers

```php
public function show(Vehicle $vehicle): JsonResponse
{
    $dto = VehicleDTO::fromModel($vehicle);

    return response()->json($dto->toArray());
}

public function index(Request $request): JsonResponse
{
    $vehicles = Vehicle::with(['images', 'reviews'])
        ->paginate(15);

    $dtos = $vehicles->map(fn($v) => VehicleDTO::fromModel($v));

    return response()->json([
        'data' => $dtos,
        'meta' => [
            'current_page' => $vehicles->currentPage(),
            'total' => $vehicles->total(),
        ],
    ]);
}
```

---

## Best Practices

### Enum Usage

1. **Always use enums for fixed sets of values**
   ```php
   // ✅ Good
   $user->role = UserRole::ADMIN;

   // ❌ Bad
   $user->role = 'admin';
   ```

2. **Leverage enum methods**
   ```php
   // ✅ Good
   BadgeColumn::make('status')
       ->formatStateUsing(fn($state) => $state->label())
       ->color(fn($state) => $state->color());

   // ❌ Bad
   BadgeColumn::make('status')
       ->formatStateUsing(fn($state) => match($state) {
           'active' => 'Active',
           // Duplicating enum logic
       });
   ```

3. **Use type hints**
   ```php
   // ✅ Good
   public function updateStatus(BookingStatus $status): void

   // ❌ Bad
   public function updateStatus(string $status): void
   ```

### DTO Usage

1. **Use DTOs for API responses**
   - Consistent structure
   - Hide internal fields
   - Transform data appropriately

2. **Keep DTOs readonly**
   - Immutability ensures data integrity
   - Use `readonly` keyword (PHP 8.2+)

3. **Provide helper methods**
   - `fromModel()` - Create from Eloquent
   - `toArray()` - Convert to array
   - `toJson()` - Convert to JSON

---

**Last Updated**: 2025-11-11
