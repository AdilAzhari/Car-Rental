<?php

namespace App\Enums;

enum VehicleCategory: string
{
    case ECONOMY = 'economy';
    case COMPACT = 'compact';
    case MIDSIZE = 'midsize';
    case FULLSIZE = 'fullsize';
    case LUXURY = 'luxury';
    case SUV = 'suv';
    case MINIVAN = 'minivan';
    case PICKUP = 'pickup';
    case SPORTS = 'sports';

    public function label(): string
    {
        return match ($this) {
        self::ECONOMY => __('enums.user_status.pending'),
        self::COMPACT => __('enums.user_status.approved'),
        self::MIDSIZE => __('enums.user_status.rejected'),
        self::FULLSIZE => __('enums.user_status.active'),
        self::SUV => __('enums.user_status.pending'),
        self::MINIVAN => __('enums.user_status.approved'),
        self::PICKUP => __('enums.user_status.rejected'),
        self::SPORTS => __('enums.user_status.active'),
       };
    }

    public static function values(): array
    {
        return array_map(fn (\App\Enums\VehicleCategory $vehicleCategory) => $vehicleCategory->value, self::cases());
    }

}

