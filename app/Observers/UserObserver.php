<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\NewUserRegistered;

class UserObserver
{
    public function created(User $user): void
    {
        // Notify all admins about new user registration
        $admins = User::where('role', UserRole::ADMIN)
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewUserRegistered($user));
        }
    }
}
