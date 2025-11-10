<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case RENTER = 'renter';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => __('enums.user_role.admin'),
            self::OWNER => __('enums.user_role.owner'),
            self::RENTER => __('enums.user_role.renter'),
        };
    }

    public static function values(): array
    {
        return array_map(fn (UserRole $userRole) => $userRole->value, self::cases());
    }
}
