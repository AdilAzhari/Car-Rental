<?php

return [
    'navigation_label' => 'Vehicles',
    'navigation_group' => 'Fleet Management',
    'model_label' => 'Vehicle',
    'plural_model_label' => 'Vehicles',
    'none' => 'None',

    'fields' => [
        'owner' => 'Owner',
        'make' => 'Make',
        'model' => 'Model',
        'year' => 'Year',
        'plate_number' => 'Plate Number',
        'vin' => 'VIN',
        'fuel_type' => 'Fuel Type',
        'transmission' => 'Transmission',
        'daily_rate' => 'Daily Rate',
        'oil_type' => 'Oil Type',
        'last_oil_change' => 'Last Oil Change',
        'policy' => 'Policy',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'tabs' => [
        'basic_information' => 'Basic Information',
        'identification' => 'Identification',
        'specifications' => 'Specifications',
        'operations' => 'Operations',
        'status_approval' => 'Status & Approval',
    ],

    'sections' => [
        'owner_details' => 'Owner Details',
        'vehicle_basics' => 'Vehicle Basics',
        'vehicle_identification' => 'Vehicle Identification',
        'vehicle_specifications' => 'Vehicle Specifications',
        'maintenance_operations' => 'Maintenance Operations',
        'status_management' => 'Status Management',
    ],

    'fuel_types' => [
        'petrol' => 'Petrol',
        'diesel' => 'Diesel',
        'electric' => 'Electric',
        'hybrid' => 'Hybrid',
    ],

    'transmission_types' => [
        'manual' => 'Manual',
        'automatic' => 'Automatic',
    ],

    'status_types' => [
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'published' => 'Published',
    ],

    'help_texts' => [
        'make' => 'Select vehicle make',
        'model' => 'Enter vehicle model',
        'year' => 'Vehicle manufacturing year',
        'plate_number' => 'Official vehicle plate number',
        'vin' => 'Vehicle Identification Number (optional)',
        'daily_rate' => 'Daily rental rate in local currency',
        'oil_type' => 'Engine oil type used',
        'last_oil_change' => 'Date of last oil change',
        'policy' => 'Rental policy and terms',
    ],

    'actions' => [
        'create_vehicle' => 'Create New Vehicle',
        'edit_vehicle' => 'Edit Vehicle',
        'delete_vehicle' => 'Delete Vehicle',
        'view_vehicle' => 'View Vehicle',
    ],
];
