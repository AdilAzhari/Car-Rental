<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewReceived;

class ReviewObserver
{
    public function created(Review $review): void
    {
        // Notify vehicle owner
        if ($review->vehicle && $review->vehicle->owner) {
            $review->vehicle->owner->notify(new NewReviewReceived($review));
        }

        // Notify admins
        $admins = User::where('role', UserRole::ADMIN)->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewReviewReceived($review));
        }
    }
}
