<?php

namespace App\Enums;

enum UserStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ACTIVE = 'active';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.user_status.pending'),
            self::APPROVED => __('enums.user_status.approved'),
            self::REJECTED => __('enums.user_status.rejected'),
            self::ACTIVE => __('enums.user_status.active'),
        };
    }

    public static function values(): array
    {
        return array_map(fn (\App\Enums\UserStatus $userStatus) => $userStatus->value, self::cases());
    }
}
