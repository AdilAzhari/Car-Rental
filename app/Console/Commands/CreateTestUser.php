<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'create:test-user {name} {email} {--role=renter} {--password=password}';

    protected $description = 'Create a test user with new user status';

    public function handle(): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $role = $this->option('role');
        $password = $this->option('password');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");

            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::from($role),
            'status' => UserStatus::ACTIVE,
            'is_new_user' => true,
            'has_changed_default_password' => false,
            'is_verified' => false,
        ]);

        $this->info('✓ Test user created successfully!');
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Role: {$user->role->value}");
        $this->line("Password: {$password}");
        $this->line('Is New User: '.($user->is_new_user ? 'Yes' : 'No'));
        $this->line('');
        $this->info('The user can now log in at /admin/login and will see the welcome notifications.');

        return 0;
    }
}
