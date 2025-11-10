<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view reviews (filtered per user)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        // Admin can view all, renters can view their own reviews, owners can view reviews for their vehicles
        return $user->role === 'admin' ||
               $review->renter_id === $user->id ||
               ($user->role === 'owner' && $review->vehicle->owner_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins and renters can create reviews
        return $user->role === 'admin' || $user->role === 'renter';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        // Admin can update all, renters can update their own reviews
        return $user->role === 'admin' || $review->renter_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        // Admin can delete all, renters can delete their own reviews
        return $user->role === 'admin' || $review->renter_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        // Only admins can restore reviews
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        // Only admins can permanently delete reviews
        return $user->role === 'admin';
    }
}
