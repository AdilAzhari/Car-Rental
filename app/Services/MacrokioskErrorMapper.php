<?php

namespace App\Services;

class MacrokioskErrorMapper
{
    /**
     * MACROKIOSK API Error Codes (from documentation)
     * Reference: Bulk SMS API Specifications v4.1
     */
    private const ERROR_CODES = [
        // Success
        200 => 'Success - Message accepted for delivery',

        // Authentication Errors (400-409)
        400 => 'Invalid username or password',
        401 => 'Account suspended',
        402 => 'Account expired',
        403 => 'IP address not allowed',
        404 => 'Invalid service ID',
        405 => 'Insufficient credit balance',
        406 => 'Invalid sender ID',
        407 => 'Service not subscribed',
        408 => 'Message quota exceeded',
        409 => 'Account locked due to security',

        // Message Errors (410-419)
        410 => 'Invalid recipient number',
        411 => 'Message too long',
        412 => 'Invalid message content',
        413 => 'Invalid message type',
        414 => 'Invalid message encoding',
        415 => 'Duplicate message detected',
        416 => 'Message expired',
        417 => 'Invalid characters in message',
        418 => 'Message blocked by spam filter',
        419 => 'Message contains blacklisted keyword',

        // System Errors (420-429)
        420 => 'System error - Please try again',
        421 => 'Database error',
        422 => 'Queue full - Retry later',
        423 => 'Gateway timeout',
        424 => 'Gateway unavailable',
        425 => 'Network error',
        426 => 'Service temporarily unavailable',
        427 => 'Rate limit exceeded',
        428 => 'Request timeout',
        429 => 'Too many requests',

        // API Errors (430-435)
        430 => 'Invalid API request format',
        431 => 'Missing required parameter',
        432 => 'Invalid parameter value',
        433 => 'API version not supported',
        434 => 'Method not allowed',
        435 => 'JWT token invalid or expired',
    ];

    /**
     * Get error message for given code
     */
    public static function getMessage(int|string $code): string
    {
        $code = (int) $code;

        return self::ERROR_CODES[$code] ?? "Unknown error code: {$code}";
    }

    /**
     * Check if code indicates success
     */
    public static function isSuccess(int|string $code): bool
    {
        return (int) $code === 200;
    }

    /**
     * Check if code indicates authentication error
     */
    public static function isAuthError(int|string $code): bool
    {
        $code = (int) $code;
        return $code >= 400 && $code <= 409;
    }

    /**
     * Check if code indicates message error
     */
    public static function isMessageError(int|string $code): bool
    {
        $code = (int) $code;
        return $code >= 410 && $code <= 419;
    }

    /**
     * Check if code indicates system error
     */
    public static function isSystemError(int|string $code): bool
    {
        $code = (int) $code;
        return $code >= 420 && $code <= 429;
    }

    /**
     * Check if code indicates API error
     */
    public static function isApiError(int|string $code): bool
    {
        $code = (int) $code;
        return $code >= 430 && $code <= 435;
    }

    /**
     * Check if error is retryable
     */
    public static function isRetryable(int|string $code): bool
    {
        $code = (int) $code;

        $retryableCodes = [
            420, // System error
            422, // Queue full
            423, // Gateway timeout
            424, // Gateway unavailable
            425, // Network error
            426, // Service temporarily unavailable
            428, // Request timeout
            429, // Too many requests
        ];

        return in_array($code, $retryableCodes);
    }

    /**
     * Get error category
     */
    public static function getCategory(int|string $code): string
    {
        if (self::isSuccess($code)) {
            return 'success';
        }

        if (self::isAuthError($code)) {
            return 'authentication';
        }

        if (self::isMessageError($code)) {
            return 'message';
        }

        if (self::isSystemError($code)) {
            return 'system';
        }

        if (self::isApiError($code)) {
            return 'api';
        }

        return 'unknown';
    }

    /**
     * Get all error codes
     */
    public static function getAllCodes(): array
    {
        return self::ERROR_CODES;
    }

    /**
     * Format error response with additional details
     */
    public static function formatError(int|string $code, ?string $additionalInfo = null): array
    {
        $code = (int) $code;

        return [
            'error_code' => $code,
            'error_message' => self::getMessage($code),
            'error_category' => self::getCategory($code),
            'is_retryable' => self::isRetryable($code),
            'additional_info' => $additionalInfo,
        ];
    }
}
