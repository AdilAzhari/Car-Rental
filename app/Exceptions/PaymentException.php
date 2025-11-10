<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentException extends Exception
{
    public function __construct(
        string $message = 'Payment operation failed',
        private readonly array $context = [],
        private readonly int $statusCode = 402
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_type' => 'payment_error',
            'context' => $this->context,
        ], $this->statusCode);
    }

    public static function processingFailed(string $reason, ?array $details = null): self
    {
        return new self(
            'Payment processing failed: '.$reason,
            ['reason' => $reason, 'details' => $details],
            402
        );
    }

    public static function insufficientFunds(): self
    {
        return new self(
            'Insufficient funds to complete the payment.',
            ['reason' => 'insufficient_funds'],
            402
        );
    }

    public static function invalidPaymentMethod(string $method): self
    {
        return new self(
            'Invalid payment method provided.',
            ['payment_method' => $method],
            400
        );
    }

    public static function refundFailed(int $paymentId, string $reason): self
    {
        return new self(
            'Refund processing failed: '.$reason,
            ['payment_id' => $paymentId, 'reason' => $reason],
            402
        );
    }

    public static function alreadyPaid(int $bookingId): self
    {
        return new self(
            'This booking has already been paid.',
            ['booking_id' => $bookingId],
            400
        );
    }

    public static function gatewayError(string $gateway, string $error): self
    {
        return new self(
            'Payment gateway error: '.$error,
            ['gateway' => $gateway, 'error' => $error],
            502
        );
    }
}
