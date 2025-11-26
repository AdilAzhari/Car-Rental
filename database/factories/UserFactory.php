<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'role' => $this->faker->randomElement([UserRole::OWNER, UserRole::RENTER]),
            'status' => $this->faker->randomElement([UserStatus::PENDING, UserStatus::APPROVED, UserStatus::ACTIVE]),
            'license_number' => $this->faker->bothify('???-####-####'),
            'id_document_path' => 'documents/ids/'.$this->faker->uuid().'.pdf',
            'license_document_path' => 'documents/licenses/'.$this->faker->uuid().'.pdf',
            'is_verified' => $this->faker->boolean(70),
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'address' => $this->faker->address(),
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
