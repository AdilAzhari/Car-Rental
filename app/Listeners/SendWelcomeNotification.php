<?php

namespace App\Listeners;

use App\Notifications\PasswordChangeReminder;
use App\Notifications\WelcomeNewUser;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(Login|Registered $event): void
    {
        $user = $event->user;

        // Always update last login time for Login events
        if ($event instanceof Login) {
            $user->update([
                'last_login_at' => now(),
            ]);
        }

        // Only send welcome notification for new users
        if ($user->is_new_user) {
            // Send welcome notification
            $user->notify(new WelcomeNewUser);

            // Schedule password change reminder for 7 days later if they haven't changed it
            if (! $user->has_changed_default_password) {
                $user->notify((new PasswordChangeReminder)->delay(now()->addDays(7)));
            }
        }
    }
}
