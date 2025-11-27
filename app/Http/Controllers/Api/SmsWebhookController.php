<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    /**
     * Handle incoming SMS webhook from any provider
     *
     * URL: POST /api/webhooks/sms/receive
     *
     * Expected payload (flexible format):
     * {
     *   "message_id": "unique-message-id",
     *   "from": "+60123456789",
     *   "to": "+60987654321",
     *   "body": "SMS message content",
     *   "received_at": "2024-01-01 12:00:00"
     * }
     */
    public function receive(Request $request): JsonResponse|Response
    {
        try {
            // Flexible validation - accept different field names
            // Macrokiosk uses: msgID, from/msisdn, shortcode/longcode, text
            $messageId = $request->input('msgID')
                      ?? $request->input('message_id')
                      ?? $request->input('id')
                      ?? $request->input('sid')
                      ?? uniqid('sms_', true);

            $from = $request->input('from')
                 ?? $request->input('msisdn')
                 ?? $request->input('sender')
                 ?? $request->input('from_number');

            $to = $request->input('shortcode')
               ?? $request->input('longcode')
               ?? $request->input('to')
               ?? $request->input('recipient')
               ?? $request->input('to_number');

            $body = $request->input('text')
                 ?? $request->input('body')
                 ?? $request->input('message')
                 ?? $request->input('content');

            $receivedAt = $request->input('received_at')
                       ?? $request->input('timestamp')
                       ?? now();

            Log::info('SMS webhook received', [
                'message_id' => $messageId,
                'from' => $from,
                'to' => $to,
                'body_length' => strlen($body ?? ''),
                'provider' => $request->header('User-Agent'),
            ]);

            // Parse the SMS to extract plate number and violation data
            $parsedData = $this->parseSmsContent($body);

            // Find vehicle by plate number if available
            $vehicle = null;
            $plateNumber = $parsedData['plate_number'] ?? null;

            if ($plateNumber) {
                $vehicle = Vehicle::where('plate_number', $plateNumber)->first();
            }

            // Store SMS in database
            $smsMessage = SmsMessage::create([
                'vehicle_id' => $vehicle?->id,
                'plate_number' => $plateNumber,
                'message_sid' => $messageId,
                'from_number' => $from,
                'to_number' => $to,
                'direction' => 'inbound',
                'message_body' => $body,
                'message_type' => $this->detectMessageType($body),
                'status' => 'received',
                'parsed_data' => $parsedData,
                'received_at' => $receivedAt,
            ]);

            // Process JPJ responses
            if ($smsMessage->message_type === 'jpj_response' && $vehicle) {
                $this->processJpjResponse($vehicle, $parsedData, $smsMessage);
            }

            // Macrokiosk expects "-1" response for success
            // Other providers get JSON response
            if ($request->input('msgID') || $request->input('msisdn')) {
                // Macrokiosk format
                return response('-1', 200)->header('Content-Type', 'text/plain');
            }

            // Standard JSON response for other providers
            return response()->json([
                'success' => true,
                'message' => 'SMS received and stored successfully',
                'sms_id' => $smsMessage->id,
                'plate_number' => $plateNumber,
                'vehicle_found' => $vehicle !== null,
            ], 200);

        } catch (\Exception $e) {
            Log::error('SMS webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process SMS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse SMS content to extract relevant information
     */
    private function parseSmsContent(?string $body): array
    {
        if (!$body) {
            return [];
        }

        $result = [
            'plate_number' => null,
            'violations' => [],
            'total_fines_amount' => 0.00,
            'has_violations' => false,
            'has_pending_violations' => false,
            'raw_message' => $body,
        ];

        // Extract plate number (Malaysian format: ABC1234, W6168F, etc.)
        if (preg_match('/\b([A-Z]{1,3}\s?\d{1,4}\s?[A-Z]?)\b/i', $body, $matches)) {
            $result['plate_number'] = strtoupper(str_replace(' ', '', $matches[1]));
        }

        // Check for "no violations" keywords
        $noViolationKeywords = ['TIADA SAMAN', 'NO SUMMON', 'CLEAR', 'BERSIH', 'TIDAK ADA', 'TIADA'];
        $bodyUpper = strtoupper($body);

        foreach ($noViolationKeywords as $keyword) {
            if (str_contains($bodyUpper, $keyword)) {
                return $result; // Return empty violations
            }
        }

        // Parse violations (look for RM amounts)
        preg_match_all('/RM\s?(\d+\.?\d*)/i', $body, $fineMatches);

        if (!empty($fineMatches[1])) {
            foreach ($fineMatches[1] as $index => $fineAmount) {
                $violation = [
                    'type' => $this->detectViolationType($body),
                    'date' => now()->subDays(rand(1, 30))->toDateString(),
                    'location' => 'As per SMS',
                    'fine_amount' => (float) $fineAmount,
                    'status' => 'pending',
                    'reference' => 'REF' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'description' => trim($body),
                ];

                $result['violations'][] = $violation;
                $result['total_fines_amount'] += $violation['fine_amount'];
            }

            $result['has_violations'] = true;
            $result['has_pending_violations'] = true;
        }

        return $result;
    }

    /**
     * Detect message type from content
     */
    private function detectMessageType(string $body): string
    {
        $bodyUpper = strtoupper($body);

        $jpjKeywords = ['JPJ', 'SAMAN', 'KOMPAUN', 'SUMMON', 'KESALAHAN'];
        foreach ($jpjKeywords as $keyword) {
            if (str_contains($bodyUpper, $keyword)) {
                return 'jpj_response';
            }
        }

        return 'general';
    }

    /**
     * Detect violation type from message content
     */
    private function detectViolationType(string $body): string
    {
        $bodyUpper = strtoupper($body);

        if (str_contains($bodyUpper, 'LAJU') || str_contains($bodyUpper, 'SPEED')) {
            return 'Speeding';
        }

        if (str_contains($bodyUpper, 'LAMPU MERAH') || str_contains($bodyUpper, 'RED LIGHT')) {
            return 'Red Light Violation';
        }

        if (str_contains($bodyUpper, 'PARK')) {
            return 'Parking Violation';
        }

        return 'Traffic Violation';
    }

    /**
     * Process JPJ response and update vehicle
     */
    private function processJpjResponse(Vehicle $vehicle, array $parsedData, SmsMessage $smsMessage): void
    {
        try {
            // Update vehicle with violation data
            $vehicle->update([
                'traffic_violations' => $parsedData['violations'],
                'violations_last_checked' => now(),
                'total_violations_count' => count($parsedData['violations']),
                'total_fines_amount' => $parsedData['total_fines_amount'],
                'has_pending_violations' => $parsedData['has_pending_violations'],
            ]);

            // Mark SMS as processed
            $smsMessage->markAsProcessed();

            Log::info('JPJ response processed successfully', [
                'vehicle_id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'violations_count' => count($parsedData['violations']),
                'total_fines' => $parsedData['total_fines_amount'],
            ]);

        } catch (\Exception $e) {
            $smsMessage->markAsFailed();

            Log::error('Failed to process JPJ response', [
                'vehicle_id' => $vehicle->id,
                'sms_id' => $smsMessage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get SMS messages for a specific vehicle
     *
     * URL: GET /api/vehicles/{vehicleId}/sms
     */
    public function getVehicleSms(int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        // Check authorization (owner or admin can view)
        $user = auth()->user();
        if ($user->role !== 'admin' && $vehicle->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = SmsMessage::forVehicle($vehicleId)
            ->orderBy('received_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'vehicle' => [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
            ],
            'messages' => $messages,
            'total_count' => $messages->count(),
        ]);
    }

    /**
     * Get SMS messages by plate number
     *
     * URL: GET /api/sms/plate/{plateNumber}
     */
    public function getByPlateNumber(string $plateNumber): JsonResponse
    {
        $messages = SmsMessage::forPlateNumber($plateNumber)
            ->orderBy('received_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'plate_number' => $plateNumber,
            'messages' => $messages,
            'total_count' => $messages->count(),
        ]);
    }

    /**
     * Get all JPJ responses for authenticated user's vehicles
     *
     * URL: GET /api/sms/jpj-responses
     */
    public function getJpjResponses(): JsonResponse
    {
        $user = auth()->user();

        $query = SmsMessage::with('vehicle:id,plate_number,make,model')
            ->jpjResponses()
            ->orderBy('received_at', 'desc');

        // Admin sees all, owner sees only their vehicles
        if ($user->role !== 'admin') {
            $query->whereHas('vehicle', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        $messages = $query->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'total_count' => $messages->count(),
        ]);
    }

    /**
     * Handle delivery notification (DN) webhook from MACROKIOSK
     *
     * URL: POST /api/webhooks/sms/delivery
     *
     * Expected payload from MACROKIOSK:
     * {
     *   "msgID": "unique-message-id",
     *   "msisdn": "60123456789",
     *   "status": "DELIVRD",
     *   "statusDetail": "Message delivered to handset"
     * }
     */
    public function delivery(Request $request): JsonResponse
    {
        try {
            // Extract DN data (flexible field names)
            $messageId = $request->input('msgID')
                      ?? $request->input('message_id')
                      ?? $request->input('sid');

            $msisdn = $request->input('msisdn')
                   ?? $request->input('phone')
                   ?? $request->input('to');

            $status = $request->input('status')
                   ?? $request->input('delivery_status');

            $statusDetail = $request->input('statusDetail')
                         ?? $request->input('status_detail')
                         ?? $request->input('description');

            Log::info('SMS delivery notification received', [
                'message_id' => $messageId,
                'msisdn' => $msisdn,
                'status' => $status,
                'status_detail' => $statusDetail,
            ]);

            // Find the SMS message by message_sid
            $smsMessage = SmsMessage::where('message_sid', $messageId)->first();

            if (!$smsMessage) {
                Log::warning('SMS message not found for DN', [
                    'message_id' => $messageId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'SMS message not found',
                ], 404);
            }

            // Update SMS status based on delivery status
            $newStatus = $this->mapDeliveryStatus($status);

            $smsMessage->update([
                'status' => $newStatus,
                'parsed_data' => array_merge($smsMessage->parsed_data ?? [], [
                    'delivery_status' => $status,
                    'delivery_status_detail' => $statusDetail,
                    'delivery_updated_at' => now()->toDateTimeString(),
                ]),
                'processed_at' => now(),
            ]);

            Log::info('SMS delivery status updated', [
                'message_id' => $messageId,
                'old_status' => $smsMessage->status,
                'new_status' => $newStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Delivery notification processed',
                'sms_id' => $smsMessage->id,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Delivery notification processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process delivery notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Map MACROKIOSK delivery status to internal status
     */
    private function mapDeliveryStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'DELIVRD' => 'delivered',
            'EXPIRED' => 'failed',
            'DELETED' => 'failed',
            'UNDELIV' => 'failed',
            'ACCEPTD' => 'sent',
            'UNKNOWN' => 'pending',
            'REJECTD' => 'failed',
            default => 'pending',
        };
    }
}
