<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case MAINTENANCE = 'maintenance';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.vehicle_status.pending'),
            self::APPROVED => __('enums.vehicle_status.approved'),
            self::REJECTED => __('enums.vehicle_status.rejected'),
            self::DRAFT => __('enums.vehicle_status.draft'),
            self::PUBLISHED => __('enums.vehicle_status.published'),
            self::MAINTENANCE => __('enums.vehicle_status.maintenance'),
            self::ARCHIVED => __('enums.vehicle_status.archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::REJECTED => 'danger',
            self::DRAFT => 'secondary',
            self::PUBLISHED => 'success',
            self::MAINTENANCE => 'primary',
            self::ARCHIVED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_map(fn (VehicleStatus $vehicleStatus) => $vehicleStatus->value, self::cases());
    }
}
