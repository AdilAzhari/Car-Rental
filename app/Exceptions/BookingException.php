<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingException extends Exception
{
    public function __construct(
        string $message = 'Booking operation failed',
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
            'error_type' => 'booking_error',
            'context' => $this->context,
        ], $this->statusCode);
    }

    public static function vehicleUnavailable(int $vehicleId, ?string $reason = null): self
    {
        return new self(
            'The selected vehicle is not available for booking.',
            ['vehicle_id' => $vehicleId, 'reason' => $reason],
            400
        );
    }

    public static function dateConflict(int $vehicleId, string $startDate, string $endDate): self
    {
        return new self(
            'The vehicle is already booked for the selected dates.',
            [
                'vehicle_id' => $vehicleId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            409
        );
    }

    public static function paymentFailed(int $bookingId, string $reason): self
    {
        return new self(
            'Payment processing failed: '.$reason,
            ['booking_id' => $bookingId, 'payment_reason' => $reason],
            402
        );
    }

    public static function unauthorized(int $bookingId, int $userId): self
    {
        return new self(
            'You are not authorized to access this booking.',
            ['booking_id' => $bookingId, 'user_id' => $userId],
            403
        );
    }
}
