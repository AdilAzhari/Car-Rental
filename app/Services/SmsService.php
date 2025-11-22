<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private readonly MacrokioskSmsService $macrokioskService;

    private readonly string $fromNumber;

    /**
     * @throws Exception
     */
    public function __construct(MacrokioskSmsService $macrokioskService)
    {
        $this->macrokioskService = $macrokioskService;
        $this->fromNumber = config('sms.default_sender', 'CarRental');
    }

    public function sendSms(string $toNumber, string $message): array
    {
        try {
            $result = $this->macrokioskService->sendSms($toNumber, $message, $this->fromNumber);

            Log::info('SMS sent successfully via Macrokiosk', [
                'to' => $toNumber,
                'message_id' => $result['data']['msgid'] ?? null,
            ]);

            return [
                'success' => $result['success'],
                'message' => $result['message'],
                'response' => [
                    'sid' => $result['data']['msgid'] ?? null,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'to' => $toNumber,
                    'from' => $this->fromNumber,
                    'date_sent' => now()->format('Y-m-d H:i:s'),
                ],
            ];

        } catch (Exception $e) {
            Log::error('SMS Service Exception', [
                'error' => $e->getMessage(),
                'to' => $toNumber,
            ]);

            return [
                'success' => false,
                'message' => 'Service error: '.$e->getMessage(),
                'response' => null,
            ];
        }
    }

    public function sendBookingNotification(string $toNumber, string $bookingReference, string $customerName): array
    {
        $message = "Dear {$customerName}, your booking {$bookingReference} has been confirmed. Thank you for choosing our service!";

        return $this->sendSms($toNumber, $message);
    }

    public function sendBookingReminder(string $toNumber, string $bookingReference, string $customerName, string $pickupDate): array
    {
        $message = "Dear {$customerName}, reminder: Your booking {$bookingReference} pickup is scheduled for {$pickupDate}. Safe travels!";

        return $this->sendSms($toNumber, $message);
    }

    public function sendBookingCancellation(string $toNumber, string $bookingReference, string $customerName): array
    {
        $message = "Dear {$customerName}, your booking {$bookingReference} has been cancelled. If you have any questions, please contact us.";

        return $this->sendSms($toNumber, $message);
    }

    public function sendTrafficCheck(string $plateNumber, string $toNumber): array
    {
        $message = "JPJ SAMAN {$plateNumber}";

        return $this->sendSms($toNumber, $message);
    }

    public function getMessageStatus(string $messageSid): array
    {
        try {
            // Macrokiosk doesn't provide message status API in the same way as Twilio
            // You would need to check via webhook responses or delivery reports
            Log::info('Message status check requested', ['message_id' => $messageSid]);

            return [
                'success' => true,
                'status' => 'unknown',
                'error_code' => null,
                'error_message' => 'Status tracking not implemented for Macrokiosk',
                'date_sent' => null,
                'date_updated' => null,
            ];

        } catch (Exception $e) {
            Log::error('Status Check Error', [
                'error' => $e->getMessage(),
                'message_id' => $messageSid,
            ]);

            return [
                'success' => false,
                'message' => 'Error checking message status: '.$e->getMessage(),
            ];
        }
    }

    public function validatePhoneNumber(string $phoneNumber): array
    {
        // Basic Malaysian phone number validation
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Malaysian numbers typically start with +60 or 60 or 0
        $isValid = preg_match('/^(\+?60|0)[1-9]\d{7,9}$/', $cleaned);

        if ($isValid) {
            // Normalize to international format
            $normalized = $cleaned;
            if (str_starts_with($cleaned, '0')) {
                $normalized = '60' . substr($cleaned, 1);
            }
            if (str_starts_with($normalized, '+60')) {
                $normalized = substr($normalized, 1);
            }

            return [
                'success' => true,
                'valid' => true,
                'phone_number' => '+' . $normalized,
                'country_code' => 'MY',
                'national_format' => $phoneNumber,
            ];
        }

        return [
            'success' => false,
            'valid' => false,
            'message' => 'Invalid Malaysian phone number format',
        ];
    }
}
