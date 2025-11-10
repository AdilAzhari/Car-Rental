<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PasswordChangeReminder;
use App\Notifications\WelcomeNewUser;
use Illuminate\Console\Command;

class TestNewUserNotifications extends Command
{
    protected $signature = 'test:new-user-notifications {user_id?}';

    protected $description = 'Test the new user notification system';

    public function handle(): int
    {
        $userId = $this->argument('user_id') ?? 1;

        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return 1;
        }

        $this->info("Testing notifications for user: {$user->name} (ID: {$user->id})");

        // Send welcome notification
        $user->notify(new WelcomeNewUser);
        $this->info('✓ Welcome notification sent');

        // Send password change reminder
        $user->notify(new PasswordChangeReminder);
        $this->info('✓ Password change reminder sent');

        // Show user status
        $this->info('User Status:');
        $this->line('- Is new user: '.($user->is_new_user ? 'Yes' : 'No'));
        $this->line('- Has changed default password: '.($user->has_changed_default_password ? 'Yes' : 'No'));
        $this->line('- Last login: '.($user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'));

        return 0;
    }
}
