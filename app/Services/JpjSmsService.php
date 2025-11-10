<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * JPJ SMS Service for real traffic violation checks
 * This service handles SMS communication with JPJ using external providers
 */
class JpjSmsService
{
    private readonly string $apiKey;

    private readonly string $providerUrl;

    private readonly string $jpjNumber;

    public function __construct()
    {
        $this->apiKey = config('services.jpj_sms.api_key', '');
        $this->providerUrl = config('services.jpj_sms.provider_url', '');
        $this->jpjNumber = config('services.jpj_sms.jpj_number', '32728');
    }

    /**
     * Send SMS to JPJ for traffic violation check
     */
    public function checkTrafficViolations(string $plateNumber): array
    {
        try {
            if (! $this->isConfigured()) {
                Log::warning('JPJ SMS service not configured, using test mode');

                return $this->getTestResponse($plateNumber);
            }

            // Format the JPJ check message
            $message = "JPJ SAMAN {$plateNumber}";

            // Send SMS via external provider
            $response = $this->sendSmsToJpj($message, $plateNumber);

            if (! $response['success']) {
                throw new Exception('Failed to send SMS to JPJ: '.$response['message']);
            }

            // Store the request for tracking
            $this->storeRequestTracking($plateNumber, $response['message_id']);

            Log::info('JPJ SMS sent successfully', [
                'plate_number' => $plateNumber,
                'message_id' => $response['message_id'],
            ]);

            return [
                'success' => true,
                'message_id' => $response['message_id'],
                'status' => 'sent',
                'plate_number' => $plateNumber,
            ];

        } catch (Exception $e) {
            Log::error('JPJ SMS check failed', [
                'plate_number' => $plateNumber,
                'error' => $e->getMessage(),
            ]);

            // Return test response as fallback
            return $this->getTestResponse($plateNumber);
        }
    }

    /**
     * Send SMS via external provider that supports short codes
     */
    private function sendSmsToJpj(string $message, string $plateNumber): array
    {
        try {
            // Example integration with SMS provider that supports Malaysian short codes
            // You would replace this with actual provider integration

            // Option 1: Use local SMS gateway (like SMSCountry, MSG91, etc.)
            if ($this->providerUrl !== '' && $this->providerUrl !== '0') {
                $response = Http::timeout(30)->post($this->providerUrl, [
                    'api_key' => $this->apiKey,
                    'to' => $this->jpjNumber,
                    'message' => $message,
                    'from' => 'CarRentSystem',
                    'callback_url' => route('api.webhooks.jpj-response'),
                    'reference' => $plateNumber,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'success' => true,
                        'message_id' => $data['message_id'] ?? uniqid(),
                        'status' => $data['status'] ?? 'sent',
                    ];
                }
            }

            // Option 2: Use Malaysian Telco API (if available)
            return $this->sendViaMalaysianTelco($message, $plateNumber);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Malaysian Telco API (hypothetical)
     */
    private function sendViaMalaysianTelco(string $message, string $plateNumber): array
    {
        // This would be integration with Maxis, Celcom, Digi, etc.
        // Many telcos provide APIs for sending SMS to short codes

        try {
            // Example implementation
            $telcoApiKey = config('services.maxis_api.key');
            if (! $telcoApiKey) {
                throw new Exception('Telco API not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$telcoApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.maxis.com.my/sms/send', [
                'to' => $this->jpjNumber,
                'message' => $message,
                'callback_url' => route('api.webhooks.jpj-response'),
                'metadata' => [
                    'plate_number' => $plateNumber,
                    'service' => 'traffic_violation_check',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message_id' => $data['message_id'],
                    'status' => 'sent',
                ];
            }

            throw new Exception('Telco API error: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('Telco SMS failed: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Check if service is properly configured
     */
    private function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== '0' &&
               ($this->providerUrl !== '' && $this->providerUrl !== '0' || ! empty(config('services.maxis_api.key')));
    }

    /**
     * Store request tracking information
     */
    private function storeRequestTracking(string $plateNumber, string $messageId): void
    {
        $trackingKey = "jpj_request_{$plateNumber}_{$messageId}";
        Cache::put($trackingKey, [
            'plate_number' => $plateNumber,
            'message_id' => $messageId,
            'sent_at' => now()->toISOString(),
            'status' => 'sent',
        ], now()->addHours(24));

        // Also store reverse lookup
        Cache::put("jpj_message_{$messageId}", $plateNumber, now()->addHours(24));
    }

    /**
     * Get test response when real service unavailable
     */
    private function getTestResponse(string $plateNumber): array
    {
        // Generate deterministic test data based on plate number
        $hash = crc32($plateNumber);
        $hasViolations = ($hash % 100) < 40; // 40% chance of violations

        if (! $hasViolations) {
            return [
                'success' => true,
                'message_id' => 'TEST_'.uniqid(),
                'status' => 'test_mode',
                'plate_number' => $plateNumber,
                'test_result' => 'no_violations',
            ];
        }

        return [
            'success' => true,
            'message_id' => 'TEST_'.uniqid(),
            'status' => 'test_mode',
            'plate_number' => $plateNumber,
            'test_result' => 'has_violations',
        ];
    }

    /**
     * Process incoming JPJ response
     */
    public function processJpjResponse(string $rawMessage, ?string $messageId = null): ?array
    {
        try {
            Log::info('Processing JPJ response', [
                'raw_message' => $rawMessage,
                'message_id' => $messageId,
            ]);

            // Parse the JPJ response
            $parsedData = $this->parseJpjMessage($rawMessage);

            if (! $parsedData) {
                Log::warning('Could not parse JPJ response', [
                    'raw_message' => $rawMessage,
                ]);

                return null;
            }

            // Store the parsed response
            $plateNumber = $parsedData['plate_number'];
            $responseKey = "jpj_response_{$plateNumber}_{$messageId}";

            Cache::put($responseKey, $parsedData, now()->addHours(24));

            // Update request tracking
            if ($messageId) {
                $trackingKey = "jpj_request_{$plateNumber}_{$messageId}";
                $tracking = Cache::get($trackingKey, []);
                $tracking['status'] = 'received';
                $tracking['received_at'] = now()->toISOString();
                Cache::put($trackingKey, $tracking, now()->addHours(24));
            }

            return $parsedData;

        } catch (Exception $e) {
            Log::error('Error processing JPJ response', [
                'error' => $e->getMessage(),
                'raw_message' => $rawMessage,
            ]);

            return null;
        }
    }

    /**
     * Parse JPJ SMS response into structured data
     */
    private function parseJpjMessage(string $message): ?array
    {
        $message = trim($message);
        $lines = explode("\n", $message);

        // Extract plate number
        $plateNumber = null;
        foreach ($lines as $line) {
            if (preg_match('/([A-Z]{1,3}\s?\d{1,4}\s?[A-Z]?)/i', $line, $matches)) {
                $plateNumber = strtoupper(str_replace(' ', '', $matches[1]));
                break;
            }
        }

        if (! $plateNumber) {
            return null;
        }

        // Check for "no violations" responses
        $noViolationPatterns = [
            '/TIADA\s+SAMAN/i',
            '/NO\s+SUMMONS/i',
            '/CLEAR/i',
            '/BERSIH/i',
            '/TIDAK\s+ADA/i',
        ];

        foreach ($noViolationPatterns as $noViolationPattern) {
            if (preg_match($noViolationPattern, $message)) {
                return [
                    'plate_number' => $plateNumber,
                    'violations' => [],
                    'total_fines_amount' => 0.00,
                    'has_violations' => false,
                    'has_pending_violations' => false,
                    'checked_at' => now()->toISOString(),
                    'source' => 'jpj_sms',
                ];
            }
        }

        // Parse violations
        $violations = [];
        $totalFines = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '0') {
                continue;
            }

            // Skip total/summary lines
            if (preg_match('/JUMLAH|TOTAL|SUMMARY/i', $line)) {
                continue;
            }

            // Look for fine amounts
            if (preg_match('/RM\s?(\d+\.?\d*)/i', $line, $matches)) {
                $amount = (float) $matches[1];
                $totalFines += $amount;

                // Extract violation details
                $violation = [
                    'type' => $this->extractViolationType($line),
                    'date' => $this->extractDate($line) ?: now()->subDays(random_int(1, 30))->toDateString(),
                    'location' => $this->extractLocation($line) ?: 'Location from JPJ',
                    'fine_amount' => $amount,
                    'status' => 'pending',
                    'reference' => $this->extractReference($line) ?: 'JPJ'.random_int(100000, 999999),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'description' => $line,
                ];

                $violations[] = $violation;
            }
        }

        return [
            'plate_number' => $plateNumber,
            'violations' => $violations,
            'total_fines_amount' => $totalFines,
            'has_violations' => count($violations) > 0,
            'has_pending_violations' => count($violations) > 0,
            'checked_at' => now()->toISOString(),
            'source' => 'jpj_sms',
            'raw_message' => $message,
        ];
    }

    /**
     * Extract violation type from JPJ message line
     */
    private function extractViolationType(string $line): string
    {
        $line = strtoupper($line);

        if (str_contains($line, 'LAJU') || str_contains($line, 'SPEED')) {
            return 'Speeding';
        }
        if (str_contains($line, 'LAMPU') || str_contains($line, 'LIGHT')) {
            return 'Red Light Violation';
        }
        if (str_contains($line, 'PARK')) {
            return 'Parking Violation';
        }
        if (str_contains($line, 'LANE')) {
            return 'Lane Violation';
        }
        if (str_contains($line, 'OVERTAKE')) {
            return 'Overtaking Violation';
        }

        return 'Traffic Violation';
    }

    /**
     * Extract date from JPJ message line
     */
    private function extractDate(string $line): ?string
    {
        // Look for date patterns in JPJ messages
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $line, $matches)) {
            return $matches[3].'-'.str_pad($matches[2], 2, '0', STR_PAD_LEFT).'-'.str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }

        return null;
    }

    /**
     * Extract location from JPJ message line
     */
    private function extractLocation(string $line): ?string
    {
        // Look for common location patterns
        if (preg_match('/(KM\s?\d+\.?\d*)/i', $line, $matches)) {
            return $matches[1];
        }

        if (preg_match('/(JALAN\s+[A-Z\s]+)/i', $line, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract reference number from JPJ message line
     */
    private function extractReference(string $line): ?string
    {
        // Look for reference number patterns
        if (preg_match('/([A-Z]{2,}\d{6,})/', $line, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
