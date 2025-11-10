<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.booking_status.pending'),
            self::CONFIRMED => __('enums.booking_status.confirmed'),
            self::ONGOING => __('enums.booking_status.ongoing'),
            self::COMPLETED => __('enums.booking_status.completed'),
            self::CANCELLED => __('enums.booking_status.cancelled'),
        };
    }

    public static function values(): array
    {
        return array_map(fn (BookingStatus $bookingStatus) => $bookingStatus->value, self::cases());
    }
}
