<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => fake()->randomElement([UserRole::OWNER, UserRole::RENTER]),
            'status' => fake()->randomElement([UserStatus::PENDING, UserStatus::APPROVED, UserStatus::ACTIVE]),
            'license_number' => fake()->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.fake()->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.fake()->uuid().'.pdf',
            'is_verified' => fake()->boolean(70),
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
            'is_verified' => true,
        ]);
    }

    public function owner(): static
    {
        return $this->state([
            'role' => UserRole::OWNER,
        ]);
    }

    public function renter(): static
    {
        return $this->state([
            'role' => UserRole::RENTER,
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status' => UserStatus::PENDING,
            'is_verified' => false,
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => UserStatus::APPROVED,
        ]);
    }

    public function active(): static
    {
        return $this->state([
            'status' => UserStatus::ACTIVE,
            'is_verified' => true,
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => UserStatus::REJECTED,
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
