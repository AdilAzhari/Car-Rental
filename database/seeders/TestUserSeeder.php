<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test renter user
        $user = User::updateOrCreate([
            'email' => 'renter@example.com',
        ], [
            'name' => 'Test Renter',
            'password' => Hash::make('password'),
            'role' => UserRole::RENTER,
            'email_verified_at' => now(),
        ]);

        $this->command->info("Created test user: {$user->name} ({$user->email}) with role: {$user->role->value}");
    }
}
