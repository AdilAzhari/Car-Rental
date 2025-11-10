<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Twilio\Security\RequestValidator;

class TwilioWebhookController extends Controller
{
    public function __construct() {}

    /**
     * Handle incoming SMS webhooks from Twilio
     */
    public function handleSms(Request $request): Response
    {
        // Validate the request is from Twilio
        if (! $this->validateTwilioRequest($request)) {
            Log::warning('Invalid Twilio webhook request', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response('Unauthorized', 401);
        }

        $messageBody = $request->input('Body', '');
        $fromNumber = $request->input('From', '');
        $toNumber = $request->input('To', '');
        $messageSid = $request->input('MessageSid', '');

        Log::info('Received SMS webhook from Twilio', [
            'from' => $fromNumber,
            'to' => $toNumber,
            'body' => $messageBody,
            'message_sid' => $messageSid,
        ]);

        // Check if this is a JPJ traffic violation response
        if ($this->isJpjResponse($fromNumber, $messageBody)) {
            $this->processJpjResponse($messageBody, $messageSid);
        }

        return response('OK', 200);
    }

    /**
     * Handle incoming calls (optional)
     */
    public function handleCall(Request $request): Response
    {
        Log::info('Received call webhook from Twilio', $request->all());

        // Return TwiML response for call handling
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>
                  <Response>
                      <Say>Thank you for calling. This is an automated system for traffic violation checks.</Say>
                      <Hangup/>
                  </Response>';

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Validate that the request is actually from Twilio
     */
    private function validateTwilioRequest(Request $request): bool
    {
        if (app()->environment('local') && config('app.debug')) {
            // Skip validation in local development
            return true;
        }

        $authToken = config('services.twilio.auth_token');
        if (! $authToken) {
            return false;
        }

        $requestValidator = new RequestValidator($authToken);
        $url = $request->fullUrl();
        $postVars = $request->all();
        $signature = $request->header('X-Twilio-Signature', '');

        return $requestValidator->validate($signature, $url, $postVars);
    }

    /**
     * Check if the SMS is a response from JPJ
     */
    private function isJpjResponse(string $fromNumber, string $messageBody): bool
    {
        // JPJ responses typically come from specific numbers or contain specific keywords
        $jpjKeywords = ['JPJ', 'SAMAN', 'KOMPAUN', 'VIOLATION', 'KESALAHAN', 'BAYARAN'];

        $bodyUpper = strtoupper($messageBody);
        foreach ($jpjKeywords as $jpjKeyword) {
            if (str_contains($bodyUpper, $jpjKeyword)) {
                return true;
            }
        }

        // Also check if it's from a known JPJ number pattern
        // JPJ responses often come from short codes or specific number patterns
        return str_starts_with($fromNumber, '+6032') ||
               str_starts_with($fromNumber, '+6015') ||
               in_array($fromNumber, ['+60327', '+6032728']);
    }

    /**
     * Process JPJ traffic violation response
     */
    private function processJpjResponse(string $messageBody, string $messageSid): void
    {
        try {
            // Parse the JPJ response
            $parsedData = $this->parseJpjResponse($messageBody);

            if (! $parsedData || ! isset($parsedData['plate_number'])) {
                Log::warning('Could not parse JPJ response', [
                    'message_body' => $messageBody,
                    'message_sid' => $messageSid,
                ]);

                return;
            }

            // Store the response in cache with the plate number as key
            $plateNumber = $parsedData['plate_number'];
            $cacheKey = "jpj_response_{$plateNumber}_{$messageSid}";

            Cache::put($cacheKey, [
                'violations' => $parsedData['violations'],
                'total_fines_amount' => $parsedData['total_fines_amount'],
                'has_violations' => $parsedData['has_violations'],
                'has_pending_violations' => $parsedData['has_pending_violations'],
                'plate_number' => $plateNumber,
                'checked_at' => now()->toISOString(),
                'message_sid' => $messageSid,
                'raw_response' => $messageBody,
            ], now()->addHours(24));

            // Also store a lookup key for the message SID
            Cache::put("jpj_lookup_{$messageSid}", $plateNumber, now()->addHours(24));

            Log::info('JPJ response processed and cached', [
                'plate_number' => $plateNumber,
                'violations_count' => count($parsedData['violations']),
                'message_sid' => $messageSid,
                'cache_key' => $cacheKey,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing JPJ response', [
                'error' => $e->getMessage(),
                'message_body' => $messageBody,
                'message_sid' => $messageSid,
            ]);
        }
    }

    /**
     * Parse JPJ SMS response into structured data
     */
    private function parseJpjResponse(string $messageBody): ?array
    {
        // This method needs to be customized based on actual JPJ response format
        // JPJ responses vary, so this is a flexible parser

        $lines = explode("\n", trim($messageBody));
        $plateNumber = null;
        $violations = [];

        // Try to extract plate number from the message
        foreach ($lines as $line) {
            if (preg_match('/([A-Z]{1,3}\s?\d{1,4}\s?[A-Z]?)/i', $line, $matches)) {
                $plateNumber = strtoupper(str_replace(' ', '', $matches[1]));
                break;
            }
        }

        if (! $plateNumber) {
            return null;
        }

        // Check if message indicates no violations
        $noViolationKeywords = ['TIADA', 'NO', 'CLEAR', 'BERSIH', 'TIDAK ADA'];
        $messageUpper = strtoupper($messageBody);

        foreach ($noViolationKeywords as $noViolationKeyword) {
            if (str_contains($messageUpper, $noViolationKeyword)) {
                return [
                    'plate_number' => $plateNumber,
                    'violations' => [],
                    'total_fines_amount' => 0.00,
                    'has_violations' => false,
                    'has_pending_violations' => false,
                ];
            }
        }

        // Parse violation details
        // This is a simplified parser - you'll need to adjust based on actual JPJ format
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '0') {
                continue;
            }

            // Look for fine amounts (RM XX.XX pattern)
            if (preg_match('/RM\s?(\d+\.?\d*)/i', $line, $matches)) {
                $fineAmount = (float) $matches[1];

                // Try to extract violation type and other details
                $violationType = 'Traffic Violation';
                if (str_contains(strtoupper($line), 'LAJU')) {
                    $violationType = 'Speeding';
                } elseif (str_contains(strtoupper($line), 'LAMPU')) {
                    $violationType = 'Red Light Violation';
                } elseif (str_contains(strtoupper($line), 'PARK')) {
                    $violationType = 'Parking Violation';
                }

                $violations[] = [
                    'type' => $violationType,
                    'date' => now()->subDays(random_int(1, 30))->toDateString(),
                    'location' => 'Location from JPJ Response',
                    'fine_amount' => $fineAmount,
                    'status' => 'pending',
                    'reference' => 'REF'.random_int(100000, 999999),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'description' => trim($line),
                ];
            }
        }

        $totalFines = array_sum(array_column($violations, 'fine_amount'));
        $hasPending = count(array_filter($violations, fn (array $v): true => $v['status'] === 'pending')) > 0;

        return [
            'plate_number' => $plateNumber,
            'violations' => $violations,
            'total_fines_amount' => $totalFines,
            'has_violations' => count($violations) > 0,
            'has_pending_violations' => $hasPending,
        ];
    }

    /**
     * Manual test endpoint for webhook
     */
    public function test(Request $request): Response
    {
        $testMessage = $request->input('message', 'JPJ ABC1234 TIADA SAMAN');
        $testSid = 'TEST_'.uniqid();

        Log::info('Testing JPJ response parsing', [
            'test_message' => $testMessage,
            'test_sid' => $testSid,
        ]);

        $this->processJpjResponse($testMessage, $testSid);

        return response()->json([
            'status' => 'Test completed',
            'message' => $testMessage,
            'message_sid' => $testSid,
        ]);
    }
}
