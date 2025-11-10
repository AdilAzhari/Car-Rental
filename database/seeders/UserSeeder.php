<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::query()->firstOrCreate(
            ['email' => 'admin@carrental.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin123@'),
                'phone' => '+1-555-000-0001',
                'role' => UserRole::ADMIN,
                'status' => UserStatus::ACTIVE,
                'is_verified' => true,
                'date_of_birth' => '1980-01-15',
                'address' => '123 Admin Boulevard, Corporate City, CC 10001',
            ]
        );

        // Create test owner accounts with different verification states
        $testOwners = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1-555-001-0001',
                'status' => UserStatus::ACTIVE,
                'is_verified' => true,
                'license_number' => 'DL-001-2024-001',
                'date_of_birth' => '1985-03-20',
                'address' => '456 Oak Street, Springfield, IL 62701',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+1-555-001-0002',
                'status' => UserStatus::ACTIVE,
                'is_verified' => true,
                'license_number' => 'DL-001-2024-002',
                'date_of_birth' => '1978-07-12',
                'address' => '789 Maple Avenue, Chicago, IL 60601',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1-555-001-0003',
                'status' => UserStatus::PENDING,
                'is_verified' => false,
                'license_number' => 'DL-001-2024-003',
                'date_of_birth' => '1990-11-05',
                'address' => '321 Pine Road, Houston, TX 77001',
            ],
        ];

        foreach ($testOwners as $testOwner) {
            User::query()->firstOrCreate(['email' => $testOwner['email']], array_merge($testOwner, [
                'password' => Hash::make('password123'),
                'role' => UserRole::OWNER,
                'id_document_path' => 'documents/ids/sample-id-'.uniqid().'.pdf',
                'license_document_path' => 'documents/licenses/sample-license-'.uniqid().'.pdf',
            ]));
        }

        // Create test renter accounts with different scenarios
        $testRenters = [
            [
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@example.com',
                'phone' => '+1-555-002-0001',
                'status' => UserStatus::ACTIVE,
                'is_verified' => true,
                'license_number' => 'DL-002-2024-001',
                'date_of_birth' => '1992-05-18',
                'address' => '654 Cedar Lane, Los Angeles, CA 90210',
            ],
            [
                'name' => 'David Lee',
                'email' => 'david.lee@example.com',
                'phone' => '+1-555-002-0002',
                'status' => UserStatus::APPROVED,
                'is_verified' => true,
                'license_number' => 'DL-002-2024-002',
                'date_of_birth' => '1988-12-03',
                'address' => '987 Birch Street, New York, NY 10001',
            ],
            [
                'name' => 'Lisa Davis',
                'email' => 'lisa.davis@example.com',
                'phone' => '+1-555-002-0003',
                'status' => UserStatus::REJECTED,
                'is_verified' => false,
                'license_number' => 'DL-002-2024-003',
                'date_of_birth' => '1995-08-25',
                'address' => '147 Elm Drive, Miami, FL 33101',
            ],
        ];

        foreach ($testRenters as $testRenter) {
            User::query()->firstOrCreate(['email' => $testRenter['email']], array_merge($testRenter, [
                'password' => Hash::make('password123'),
                'role' => UserRole::RENTER,
                'id_document_path' => 'documents/ids/sample-id-'.uniqid().'.pdf',
                'license_document_path' => 'documents/licenses/sample-license-'.uniqid().'.pdf',
            ]));
        }

        // Create additional random users for testing with various statuses
        User::factory(5)->owner()->active()->create();
        User::factory(5)->owner()->approved()->create();
        User::factory(5)->owner()->pending()->create();

        User::factory(8)->renter()->active()->create();
        User::factory(8)->renter()->approved()->create();
        User::factory(6)->renter()->pending()->create();
        User::factory(3)->renter()->rejected()->create();

        // Create some unverified users for testing verification process
        User::factory(3)->owner()->pending()->unverified()->create();
        User::factory(5)->renter()->pending()->unverified()->create();
    }
}
