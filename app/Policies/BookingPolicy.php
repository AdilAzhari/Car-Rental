<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view bookings (filtered per user)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Admin can view all, renters can view their own bookings, owners can view bookings for their vehicles
        return $user->role === UserRole::ADMIN ||
               $booking->renter_id === $user->id ||
               ($user->role === UserRole::OWNER && $booking->vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins and renters can create bookings
        return $user->role === UserRole::ADMIN || $user->role === UserRole::RENTER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Admin can update all, renters can update their own bookings, owners can update bookings for their vehicles
        return $user->role === UserRole::ADMIN ||
               $booking->renter_id === $user->id ||
               ($user->role === UserRole::OWNER && $booking->vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Admin can delete all, renters can cancel their own bookings
        return $user->role === UserRole::ADMIN ||
               ($booking->renter_id === $user->id && in_array($booking->status, [BookingStatus::PENDING, BookingStatus::CONFIRMED]));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        // Only admins can restore bookings
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        // Only admins can permanently delete bookings
        return $user->role === UserRole::ADMIN;
    }
}
