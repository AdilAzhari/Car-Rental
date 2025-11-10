<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class SmsService
{
    private readonly Client $client;

    private readonly string $fromNumber;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $fromNumber = config('services.twilio.from_number');

        if (! $accountSid || ! $authToken || ! $fromNumber) {
            throw new Exception('Twilio credentials (account_sid, auth_token, from_number) not configured');
        }

        $this->fromNumber = $fromNumber;
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendSms(string $toNumber, string $message): array
    {
        try {
            $twilioMessage = $this->client->messages->create(
                $toNumber,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );

            Log::info('SMS sent successfully via Twilio', [
                'to' => $toNumber,
                'sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => [
                    'sid' => $twilioMessage->sid,
                    'status' => $twilioMessage->status,
                    'to' => $twilioMessage->to,
                    'from' => $twilioMessage->from,
                    'date_sent' => $twilioMessage->dateSent?->format('Y-m-d H:i:s'),
                ],
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio SMS Error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'to' => $toNumber,
            ]);

            return [
                'success' => false,
                'message' => 'Twilio error: '.$e->getMessage(),
                'response' => null,
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
            $message = $this->client->messages($messageSid)->fetch();

            return [
                'success' => true,
                'status' => $message->status,
                'error_code' => $message->errorCode,
                'error_message' => $message->errorMessage,
                'date_sent' => $message->dateSent?->format('Y-m-d H:i:s'),
                'date_updated' => $message->dateUpdated?->format('Y-m-d H:i:s'),
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio Status Check Error', [
                'error' => $e->getMessage(),
                'sid' => $messageSid,
            ]);

            return [
                'success' => false,
                'message' => 'Error checking message status: '.$e->getMessage(),
            ];
        }
    }

    public function validatePhoneNumber(string $phoneNumber): array
    {
        try {
            // Use Twilio Lookup API to validate phone number
            $phone = $this->client->lookups->v1->phoneNumbers($phoneNumber)->fetch();

            return [
                'success' => true,
                'valid' => true,
                'phone_number' => $phone->phoneNumber,
                'country_code' => $phone->countryCode,
                'national_format' => $phone->nationalFormat,
            ];

        } catch (TwilioException $e) {
            return [
                'success' => false,
                'valid' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
