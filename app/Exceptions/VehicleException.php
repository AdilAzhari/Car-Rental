<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleException extends Exception
{
    public function __construct(
        string $message = 'Vehicle operation failed',
        private readonly array $context = [],
        private readonly int $statusCode = 400
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_type' => 'vehicle_error',
            'context' => $this->context,
        ], $this->statusCode);
    }

    public static function notFound(int $vehicleId): self
    {
        return new self(
            'Vehicle not found.',
            ['vehicle_id' => $vehicleId],
            404
        );
    }

    public static function notAvailable(int $vehicleId, ?string $reason = null): self
    {
        return new self(
            'Vehicle is not available for rent.',
            ['vehicle_id' => $vehicleId, 'reason' => $reason],
            400
        );
    }

    public static function insuranceExpired(int $vehicleId, string $expiryDate): self
    {
        return new self(
            'Vehicle insurance has expired and cannot be rented.',
            ['vehicle_id' => $vehicleId, 'expiry_date' => $expiryDate],
            400
        );
    }

    public static function unauthorized(int $vehicleId, int $userId): self
    {
        return new self(
            'You are not authorized to access this vehicle.',
            ['vehicle_id' => $vehicleId, 'user_id' => $userId],
            403
        );
    }

    public static function maintenanceRequired(int $vehicleId): self
    {
        return new self(
            'Vehicle requires maintenance and cannot be rented.',
            ['vehicle_id' => $vehicleId],
            400
        );
    }
}
