<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';
    case PROCESSING = 'processing';

    case UNPAID = 'unpaid';

    /**
     * Get all enum values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get display label for the payment status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.payment_status.pending'),
            self::CONFIRMED => __('enums.payment_status.confirmed'),
            self::FAILED => __('enums.payment_status.failed'),
            self::REFUNDED => __('enums.payment_status.refunded'),
            self::CANCELLED => __('enums.payment_status.cancelled'),
            self::PROCESSING => __('enums.payment_status.processing'),
            self::UNPAID => __('enums.payment_status.unpaid'),
        };
    }

    /**
     * Get color for status display
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
            self::CANCELLED => 'secondary',
            self::PROCESSING, self::UNPAID => 'primary',
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::CONFIRMED;
    }

    /**
     * Check if payment is final (no further processing)
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::CONFIRMED,
            self::FAILED,
            self::REFUNDED,
            self::CANCELLED,
        ], true);
    }

    /**
     * Get icon for payment status
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::CONFIRMED => 'check-circle',
            self::FAILED => 'x-circle',
            self::REFUNDED => 'arrow-path',
            self::CANCELLED => 'minus-circle',
            self::PROCESSING => 'arrows-right-left',
            self::UNPAID => 'arrows-right-right',
        };
    }
}
