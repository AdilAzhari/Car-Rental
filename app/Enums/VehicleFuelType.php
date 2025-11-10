<?php

namespace App\Enums;

enum VehicleFuelType: string
{
    case PETROL = 'petrol';
    case DIESEL = 'diesel';
    case ELECTRIC = 'electric';
    case HYBRID = 'hybrid';
    case LPG = 'lpg';

    public function label(): string
    {
        return match ($this) {
            self::PETROL => 'Petrol',
            self::DIESEL => 'Diesel',
            self::ELECTRIC => 'Electric',
            self::HYBRID => 'Hybrid',
            self::LPG => 'LPG',
        };
    }

    public static function values(): array
    {
        return array_map(fn (\App\Enums\VehicleFuelType $vehicleFuelType) => $vehicleFuelType->value, self::cases());
    }
}
