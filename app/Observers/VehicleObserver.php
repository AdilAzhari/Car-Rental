<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\VehicleCreated;
use App\Notifications\VehicleStatusChanged;

class VehicleObserver
{
    /**
     * Handle the Vehicle "created" event.
     */
    public function created(Vehicle $vehicle): void
    {
        // Notify the vehicle owner about the new vehicle
        if ($vehicle->owner) {
            $vehicle->owner->notify(new VehicleCreated($vehicle));
        }

        // Also notify the first user (assuming it's an admin) as a fallback
        $firstUser = User::query()->first();
        if ($firstUser && $firstUser->id !== $vehicle->owner_id) {
            $firstUser->notify(new VehicleCreated($vehicle));
        }
    }

    /**
     * Handle the Vehicle "updated" event.
     */
    public function updated(Vehicle $vehicle): void
    {
        // Check if status changed
        if ($vehicle->isDirty('status')) {
            $oldStatus = $vehicle->getOriginal('status');
            $newStatus = $vehicle->status;

            // Convert enum objects to their string values
            $oldStatusValue = $oldStatus instanceof VehicleStatus ? $oldStatus->value : $oldStatus;
            $newStatusValue = $newStatus instanceof VehicleStatus ? $newStatus->value : $newStatus;

            // Notify vehicle owner
            if ($vehicle->owner) {
                $vehicle->owner->notify(new VehicleStatusChanged($vehicle, $oldStatusValue, $newStatusValue));
            }

            // Notify admins
            $admins = User::where('role', UserRole::ADMIN)->get();
            foreach ($admins as $admin) {
                $admin->notify(new VehicleStatusChanged($vehicle, $oldStatusValue, $newStatusValue));
            }
        }
    }
}
