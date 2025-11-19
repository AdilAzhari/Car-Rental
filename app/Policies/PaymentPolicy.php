<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view payments (filtered per user)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        // Admin can view all payments
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        // Renters can view payments for their own bookings
        if ($payment->booking->renter_id === $user->id) {
            return true;
        }

        // Owners can view payments for bookings on their vehicles
        if ($user->role === UserRole::OWNER && $payment->booking->vehicle->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins and renters can create payments (typically for their bookings)
        return $user->role === UserRole::ADMIN || $user->role === UserRole::RENTER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        // Admin can update all payments
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        // Renters can update their own pending payments
        if ($payment->booking->renter_id === $user->id) {
            return true;
        }

        // Owners can update payments for their vehicle bookings (e.g., confirm receipt)
        if ($user->role === UserRole::OWNER && $payment->booking->vehicle->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Only admins can delete payments
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $payment): bool
    {
        // Only admins can restore payments
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        // Only admins can permanently delete payments
        return $user->role === UserRole::ADMIN;
    }
}
