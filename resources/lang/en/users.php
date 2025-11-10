<?php

return [
    'singular' => 'User',
    'plural' => 'Users',

    'sections' => [
        'personal_information' => 'Personal Information',
        'account_settings' => 'Account Settings',
        'contact_information' => 'Contact Information',
        'verification_status' => 'Verification Status',
        'profile_details' => 'Profile Details',
    ],

    'fields' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'date_of_birth' => 'Date of Birth',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State/Province',
        'country' => 'Country',
        'postal_code' => 'Postal Code',
        'role' => 'User Role',
        'status' => 'Account Status',
        'email_verified_at' => 'Email Verified At',
        'phone_verified_at' => 'Phone Verified At',
        'created_at' => 'Registration Date',
        'updated_at' => 'Last Updated',
        'is_active' => 'Account Active',
        'profile_photo' => 'Profile Photo',
    ],

    'roles' => [
        'admin' => 'Administrator',
        'owner' => 'Vehicle Owner',
        'customer' => 'Customer',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'pending' => 'Pending',
    ],
];
