<?php

namespace App\Services;

use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MacrokioskSmsService
{
    private ?string $baseUrl;
    private ?string $username;
    private ?string $password;
    private ?string $serviceId;
    private ?string $sendEndpoint;
    private ?string $defaultSender;
    private bool $useJwt;
    private int $maxRetries;
    private int $retryDelay;
    private int $asciiMaxLength;
    private int $unicodeMaxLength;
    private bool $isConfigured = false;

    private ?MacrokioskJwtService $jwtService = null;

    public function __construct()
    {
        $config = config('sms.macrokiosk');

        $this->baseUrl = $config['base_url'] ?? null;
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->serviceId = $config['service_id'] ?? null;
        $this->sendEndpoint = $config['send_endpoint'] ?? '/Send';
        $this->defaultSender = $config['default_sender'] ?? 'CarRental';
        $this->useJwt = $config['use_jwt'] ?? false;
        $this->maxRetries = $config['retry_attempts'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 2;
        $this->asciiMaxLength = $config['ascii_max_length'] ?? 1071;
        $this->unicodeMaxLength = $config['unicode_max_length'] ?? 1000;

        // Check if service is properly configured
        $this->isConfigured = !empty($this->username) && !empty($this->password) && !empty($this->serviceId);

        if ($this->useJwt && $this->isConfigured) {
            $this->jwtService = new MacrokioskJwtService();
        }
    }

    /**
     * Check if SMS service is configured
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Send SMS message
     *
     * @param string|array $to Phone number(s) - can be single or array
     * @param string $message Message content
     * @param string|null $sender Sender ID (optional, uses default if not provided)
     * @return array Response with success status and details
     */
    public function send(string|array $to, string $message, ?string $sender = null): array
    {
        // Check if service is configured
        if (!$this->isConfigured) {
            Log::warning('SMS service not configured', [
                'to' => $to,
                'message_preview' => substr($message, 0, 50),
            ]);

            return [
                'success' => false,
                'error' => 'SMS service is not configured. Please set SMS_USERNAME, SMS_PASSWORD, and SMS_SERVICE_ID in .env',
                'error_code' => 'NOT_CONFIGURED',
            ];
        }

        try {
            // Normalize phone numbers to array
            $recipients = is_array($to) ? $to : [$to];

            // Clean and validate phone numbers
            $recipients = array_map(fn($num) => $this->normalizePhoneNumber($num), $recipients);

            // Use default sender if not provided
            $sender = $sender ?? $this->defaultSender;

            // Detect message type (ASCII or Unicode)
            $messageType = $this->detectMessageType($message);

            // Validate message length
            $this->validateMessageLength($message, $messageType);

            // Prepare request data
            $data = $this->prepareRequestData($recipients, $message, $sender, $messageType);

            // Send with retry logic
            $response = $this->sendWithRetry($data);

            // Parse response
            $result = $this->parseResponse($response);

            // Log the SMS
            $this->logSms($recipients, $message, $sender, $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'to' => $to,
                'message_preview' => substr($message, 0, 50),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'EXCEPTION',
            ];
        }
    }

    /**
     * Send SMS with retry logic
     */
    private function sendWithRetry(array $data): \Illuminate\Http\Client\Response
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $attempt++;

                Log::info('Sending SMS', [
                    'attempt' => $attempt,
                    'to' => $data['to'] ?? $data['msisdn'],
                    'sender' => $data['from'] ?? $data['sender'],
                ]);

                $url = $this->baseUrl . $this->sendEndpoint;

                // Build HTTP request
                $request = Http::timeout(30);

                // Add authentication
                if ($this->useJwt && $this->jwtService) {
                    $token = $this->jwtService->getToken();
                    if (!$token) {
                        throw new \Exception('Failed to obtain JWT token');
                    }
                    $request->withToken($token);
                } else {
                    // Basic authentication
                    $data['username'] = $this->username;
                    $data['password'] = $this->password;
                }

                // Send request
                $response = $request->asForm()->post($url, $data);

                // Check response
                if ($response->successful()) {
                    return $response;
                }

                // If JWT token expired (401/403), invalidate and retry
                if ($this->useJwt && in_array($response->status(), [401, 403])) {
                    Log::warning('JWT token may be expired, invalidating cache');
                    $this->jwtService?->invalidateToken();
                }

                Log::warning('SMS send attempt failed', [
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('SMS send attempt exception', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
            }

            // Wait before retry (except on last attempt)
            if ($attempt < $this->maxRetries) {
                sleep($this->retryDelay);
            }
        }

        // All retries failed
        throw new \Exception('All SMS send attempts failed: ' . ($lastException?->getMessage() ?? 'Unknown error'));
    }

    /**
     * Prepare request data for MACROKIOSK API
     */
    private function prepareRequestData(array $recipients, string $message, string $sender, int $messageType): array
    {
        $data = [
            'msisdn' => implode(',', $recipients), // Comma-separated for bulk
            'sid' => $this->serviceId,
            'msg' => $messageType === 0 ? urlencode($message) : bin2hex(mb_convert_encoding($message, 'UTF-16BE')),
            'fl' => 0, // Flash message flag (0 = normal)
            'gwid' => $sender,
            'type' => $messageType, // 0 = ASCII, 5 = Unicode
        ];

        return $data;
    }

    /**
     * Parse MACROKIOSK API response
     */
    private function parseResponse(\Illuminate\Http\Client\Response $response): array
    {
        $body = $response->body();
        $statusCode = $response->status();

        // Try to parse as JSON first
        if ($response->header('Content-Type') === 'application/json') {
            $data = $response->json();
        } else {
            // Parse text response format: "ErrorCode: Message"
            $data = ['raw' => $body];

            if (preg_match('/^(\d+):(.+)$/m', $body, $matches)) {
                $data['error_code'] = $matches[1];
                $data['message'] = trim($matches[2]);
            }
        }

        // Determine success based on error code
        $errorCode = $data['error_code'] ?? $statusCode;
        $success = in_array($errorCode, [200, '200']); // 200 = Success

        return [
            'success' => $success,
            'error_code' => $errorCode,
            'message' => $data['message'] ?? $data['raw'] ?? 'Unknown response',
            'raw_response' => $body,
        ];
    }

    /**
     * Log SMS message to database
     */
    private function logSms(array $recipients, string $message, string $sender, array $result): void
    {
        try {
            foreach ($recipients as $recipient) {
                SmsMessage::create([
                    'vehicle_id' => null,
                    'plate_number' => null,
                    'message_sid' => $result['message_id'] ?? uniqid('sms_', true),
                    'from_number' => $sender,
                    'to_number' => $recipient,
                    'direction' => 'outbound',
                    'message_body' => $message,
                    'message_type' => 'jpj_query',
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'parsed_data' => $result,
                    'received_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log SMS to database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Detect message type (ASCII or Unicode)
     */
    private function detectMessageType(string $message): int
    {
        // Check if message contains non-ASCII characters
        if (preg_match('/[^\x00-\x7F]/', $message)) {
            return 5; // Unicode
        }

        return 0; // ASCII
    }

    /**
     * Validate message length
     */
    private function validateMessageLength(string $message, int $messageType): void
    {
        $maxLength = $messageType === 0 ? $this->asciiMaxLength : $this->unicodeMaxLength;
        $currentLength = mb_strlen($message);

        if ($currentLength > $maxLength) {
            throw new \Exception("Message too long: {$currentLength} characters (max: {$maxLength})");
        }
    }

    /**
     * Normalize phone number (add country code if needed)
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);

        // Add Malaysia country code if not present
        if (!str_starts_with($phone, '+') && !str_starts_with($phone, '60')) {
            // Assuming Malaysian numbers starting with 01
            if (str_starts_with($phone, '0')) {
                $phone = '6' . $phone;
            } else {
                $phone = '60' . $phone;
            }
        }

        // Remove + if present (MACROKIOSK uses plain numbers)
        $phone = str_replace('+', '', $phone);

        return $phone;
    }

    /**
     * Send JPJ traffic violation check
     *
     * @param string $plateNumber Vehicle plate number
     * @param string $toNumber JPJ SMS number
     * @return array Response
     */
    public function checkTrafficViolations(string $plateNumber, string $toNumber = '15888'): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'SMS service is not configured',
                'error_code' => 'NOT_CONFIGURED',
            ];
        }

        // Format: JPJ <PLATE_NUMBER>
        $message = 'JPJ ' . strtoupper($plateNumber);

        return $this->send($toNumber, $message);
    }
}
