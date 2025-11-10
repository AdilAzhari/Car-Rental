<?php

namespace App\Enums;

enum VehicleTransmission: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
    case CVT = 'cvt';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Automatic',
            self::CVT => 'CVT',
        };
    }

    public static function values(): array
    {
        return array_map(fn (\App\Enums\VehicleTransmission $vehicleTransmission) => $vehicleTransmission->value, self::cases());
    }
}
