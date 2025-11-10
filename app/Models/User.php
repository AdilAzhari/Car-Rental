<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'car_rental_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'license_number',
        'id_document_path',
        'license_document_path',
        'avatar',
        'is_verified',
        'date_of_birth',
        'address',
        'is_new_user',
        'has_changed_default_password',
        'last_login_at',
        'password_changed_at',
        'license_expiry_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'date_of_birth' => 'date',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'is_new_user' => 'boolean',
            'has_changed_default_password' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Booking::class, 'renter_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'renter_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'renter_id');
    }

    public function ownedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'renter_id');
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === UserRole::ADMIN || $this->role === UserRole::OWNER;
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role->value === $role ||
               ($role === 'admin' && $this->role === UserRole::ADMIN) ||
               ($role === 'owner' && $this->role === UserRole::OWNER) ||
               ($role === 'renter' && $this->role === UserRole::RENTER);
    }
}
