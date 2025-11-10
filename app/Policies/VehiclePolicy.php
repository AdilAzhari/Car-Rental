<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // Admin can view all, owners can view their own vehicles, renters can view published vehicles
        return $user->role === UserRole::ADMIN ||
               ($user->role === UserRole::OWNER && $vehicle->owner_id === $user->id) ||
               ($user->role === UserRole::RENTER && $vehicle->status === VehicleStatus::PUBLISHED);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins and owners can create vehicles
        return $user->role === UserRole::ADMIN || $user->role === UserRole::OWNER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // Admin can update all, owners can only update their own vehicles
        return $user->role === UserRole::ADMIN ||
               ($user->role === UserRole::OWNER && $vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Admin can delete all, owners can only delete their own vehicles
        return $user->role === UserRole::ADMIN ||
               ($user->role === UserRole::OWNER && $vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        // Admin can restore all, owners can only restore their own vehicles
        return $user->role === UserRole::ADMIN ||
               ($user->role === UserRole::OWNER && $vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        // Only admins can permanently delete vehicles
        return $user->role === UserRole::ADMIN;
    }
}
