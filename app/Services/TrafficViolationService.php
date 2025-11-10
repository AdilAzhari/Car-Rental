<?php

namespace App\Services;

use App\Models\Vehicle;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Random\RandomException;

readonly class TrafficViolationService
{
    public function __construct(private ?SmsService $smsService) {}

    /**
     * Check traffic violations for a specific vehicle using SMS
     */
    public function checkVehicleViolations(Vehicle $vehicle): array
    {
        $plateNumber = $vehicle->plate_number;
        $cacheKey = "traffic_violations_$plateNumber";

        // Check cache first (24-hour cache)
        if (Cache::has($cacheKey)) {
            Log::info('Traffic violations retrieved from cache', ['plate_number' => $plateNumber]);

            return Cache::get($cacheKey);
        }

        try {

            // Check if SMS service is available
            if (! $this->smsService instanceof SmsService) {
                Log::warning('SMS service not available - using test mode for violations', ['plate_number' => $plateNumber]);

                // Use test mode - directly process violation response without SMS
                $violationsData = $this->processViolationResponse($plateNumber);
            } else {
                // Try to get real JPJ data via SMS integration
                $violationsData = $this->checkViaRealSms($plateNumber);

                if (! $violationsData) {
                    Log::warning('Real SMS check failed, falling back to test mode', [
                        'plate_number' => $plateNumber,
                    ]);
                    // Fallback to test mode if real SMS fails
                    $violationsData = $this->processViolationResponse($plateNumber);
                }
            }

            // Cache the result for 24 hours
            Cache::put($cacheKey, $violationsData, now()->addHours(24));

            Log::info('Traffic violations checked and cached', [
                'plate_number' => $plateNumber,
                'violations_count' => count($violationsData['violations']),
                'total_fines' => $violationsData['total_fines_amount'],
            ]);

            return $violationsData;

        } catch (Exception $e) {
            Log::error('Traffic violation check failed', [
                'plate_number' => $plateNumber,
                'error' => $e->getMessage(),
            ]);

            // Return empty result on error
            return $this->getEmptyViolationResult();
        }
    }

    /**
     * Update violations for all vehicles
     */
    public function updateAllVehicleViolations(): array
    {
        $results = [
            'total_checked' => 0,
            'violations_found' => 0,
            'errors' => 0,
            'cached_results' => 0,
            'new_checks' => 0,
        ];

        $vehicles = Vehicle::query()->whereNotNull('plate_number')->get();

        foreach ($vehicles as $vehicle) {
            $results['total_checked']++;

            $cacheKey = "traffic_violations_$vehicle->plate_number";
            $wasCached = Cache::has($cacheKey);

            $violationData = $this->checkVehicleViolations($vehicle);

            if ($wasCached) {
                $results['cached_results']++;
            } else {
                $results['new_checks']++;

                // Add delay between SMS requests to avoid rate limiting
                if (! $wasCached) {
                    sleep(3);
                }
            }

            if ($violationData['has_violations']) {
                $results['violations_found']++;
            }

            // Update vehicle record
            $this->updateVehicleViolations($vehicle, $violationData);
        }

        return $results;
    }

    /**
     * Update vehicle with traffic violations data
     */
    public function updateVehicleViolations(Vehicle $vehicle, array $violationData): void
    {
        $vehicle->update([
            'traffic_violations' => $violationData['violations'],
            'violations_last_checked' => now(),
            'total_violations_count' => count($violationData['violations']),
            'total_fines_amount' => $violationData['total_fines_amount'],
            'has_pending_violations' => $violationData['has_pending_violations'],
        ]);

        Log::info('Vehicle violations updated', [
            'vehicle_id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'violations_count' => count($violationData['violations']),
            'total_fines' => $violationData['total_fines_amount'],
        ]);
    }

    /**
     * Process violation response from SMS (simulate for now)
     *
     * @throws RandomException
     */
    private function processViolationResponse(string $plateNumber, ?string $messageSid = null): array
    {
        // In a real implementation, this would parse the actual SMS response
        // For demo purposes, we'll simulate different violation scenarios

        // Force violations for specific test plates
        if (in_array(strtoupper($plateNumber), ['W6168F', 'TEST1234', 'DEMO123'])) {
            return $this->getTestViolationScenario($plateNumber, $messageSid);
        }

        $scenarios = [
            // No violations (60% chance)
            [
                'violations' => [],
                'total_fines_amount' => 0.00,
                'has_violations' => false,
                'has_pending_violations' => false,
                'weight' => 60,
            ],
            // Has pending violations (25% chance)
            [
                'violations' => [
                    [
                        'type' => 'Speeding',
                        'date' => Carbon::now()->subDays(random_int(3, 15))->toDateString(),
                        'location' => 'PLUS Highway KM '.random_int(100, 400).'.'.random_int(1, 9),
                        'fine_amount' => 150.00,
                        'status' => 'pending',
                        'reference' => 'SPD'.random_int(100000, 999999),
                        'due_date' => Carbon::now()->addDays(30)->toDateString(),
                        'description' => 'Speed limit exceeded',
                    ],
                    [
                        'type' => 'Red Light Violation',
                        'date' => Carbon::now()->subDays(random_int(5, 20))->toDateString(),
                        'location' => 'Traffic Light Junction '.chr(random_int(65, 90)),
                        'fine_amount' => 300.00,
                        'status' => 'pending',
                        'reference' => 'RLV'.random_int(100000, 999999),
                        'due_date' => Carbon::now()->addDays(25)->toDateString(),
                        'description' => 'Running red light',
                    ],
                ],
                'total_fines_amount' => 450.00,
                'has_violations' => true,
                'has_pending_violations' => true,
                'weight' => 25,
            ],
            // Has paid violations (15% chance)
            [
                'violations' => [
                    [
                        'type' => 'Parking Violation',
                        'date' => Carbon::now()->subDays(random_int(20, 40))->toDateString(),
                        'location' => 'DBKL Zone '.chr(random_int(65, 68)),
                        'fine_amount' => 80.00,
                        'status' => 'paid',
                        'reference' => 'PRK'.random_int(100000, 999999),
                        'due_date' => Carbon::now()->subDays(10)->toDateString(),
                        'paid_date' => Carbon::now()->subDays(5)->toDateString(),
                        'description' => 'Illegal parking',
                    ],
                ],
                'total_fines_amount' => 0.00,
                'has_violations' => true,
                'has_pending_violations' => false,
                'weight' => 15,
            ],
        ];

        // Weighted random selection
        $totalWeight = array_sum(array_column($scenarios, 'weight'));
        $random = random_int(1, $totalWeight);
        $currentWeight = 0;

        foreach ($scenarios as $scenario) {
            $currentWeight += $scenario['weight'];
            if ($random <= $currentWeight) {
                $selectedScenario = $scenario;
                break;
            }
        }

        // Remove weight key from result
        unset($selectedScenario['weight']);

        return array_merge($selectedScenario, [
            'plate_number' => $plateNumber,
            'checked_at' => now()->toISOString(),
            'message_sid' => $messageSid,
        ]);
    }

    /**
     * Get test violation scenario for specific plate numbers
     */
    private function getTestViolationScenario(string $plateNumber, ?string $messageSid = null): array
    {
        $testViolations = [
            [
                'type' => 'Speeding',
                'date' => Carbon::now()->subDays(7)->toDateString(),
                'location' => 'PLUS Highway KM 245.3 (Northbound)',
                'fine_amount' => 150.00,
                'status' => 'pending',
                'reference' => 'SPD'.str_replace(['W', 'F'], '', strtoupper($plateNumber)).'001',
                'due_date' => Carbon::now()->addDays(30)->toDateString(),
                'description' => 'Exceeded speed limit by 25km/h (105km/h in 80km/h zone)',
            ],
            [
                'type' => 'Red Light Violation',
                'date' => Carbon::now()->subDays(12)->toDateString(),
                'location' => 'Jalan Ampang Traffic Light Junction',
                'fine_amount' => 300.00,
                'status' => 'pending',
                'reference' => 'RLV'.str_replace(['W', 'F'], '', strtoupper($plateNumber)).'002',
                'due_date' => Carbon::now()->addDays(25)->toDateString(),
                'description' => 'Failed to stop at red light signal',
            ],
            [
                'type' => 'Parking Violation',
                'date' => Carbon::now()->subDays(20)->toDateString(),
                'location' => 'DBKL Zone A - Jalan Bukit Bintang',
                'fine_amount' => 100.00,
                'status' => 'paid',
                'reference' => 'PRK'.str_replace(['W', 'F'], '', strtoupper($plateNumber)).'003',
                'due_date' => Carbon::now()->subDays(5)->toDateString(),
                'paid_date' => Carbon::now()->subDays(3)->toDateString(),
                'description' => 'Parking in restricted zone without valid permit',
            ],
        ];

        return [
            'violations' => $testViolations,
            'total_fines_amount' => 450.00, // Only pending violations count
            'has_violations' => true,
            'has_pending_violations' => true,
            'plate_number' => $plateNumber,
            'checked_at' => now()->toISOString(),
            'message_sid' => $messageSid,
        ];
    }

    /**
     * Get empty violation result for errors
     */
    private function getEmptyViolationResult(): array
    {
        return [
            'violations' => [],
            'total_fines_amount' => 0.00,
            'has_violations' => false,
            'has_pending_violations' => false,
            'checked_at' => now()->toISOString(),
            'error' => 'Failed to check violations',
        ];
    }

    /**
     * Check if vehicle has any pending violations
     */
    public function hasPendingViolations(Vehicle $vehicle): bool
    {
        if ($this->shouldRefreshViolations($vehicle)) {
            $violationData = $this->checkVehicleViolations($vehicle);
            $this->updateVehicleViolations($vehicle, $violationData);
        }

        return $vehicle->has_pending_violations;
    }

    /**
     * Get formatted violations for display
     */
    public function getFormattedViolations(Vehicle $vehicle): array
    {
        $violations = $vehicle->traffic_violations ?? [];

        return collect($violations)->map(fn ($violation): array => [
            'violation_type' => $violation['type'] ?? 'Unknown Violation',
            'date' => isset($violation['date']) ? Carbon::parse($violation['date'])->format('d M Y') : null,
            'location' => $violation['location'] ?? 'Unknown Location',
            'fine_amount' => $violation['fine_amount'] ?? 0,
            'status' => $violation['status'] ?? 'unknown',
            'reference_number' => $violation['reference'] ?? null,
            'due_date' => isset($violation['due_date']) ? Carbon::parse($violation['due_date'])->format('d M Y') : null,
            'description' => $violation['description'] ?? null,
            'paid_date' => isset($violation['paid_date']) ? Carbon::parse($violation['paid_date'])->format('d M Y') : null,
        ])->toArray();
    }

    /**
     * Check if violations data should be refreshed
     */
    protected function shouldRefreshViolations(Vehicle $vehicle): bool
    {
        if (! $vehicle->violations_last_checked) {
            return true;
        }

        return $vehicle->violations_last_checked->lt(now()->subHours(24));
    }

    /**
     * Clear cache for specific vehicle or all vehicles
     */
    public function clearCache(?string $plateNumber = null): bool
    {
        if ($plateNumber) {
            $cacheKey = "traffic_violations_$plateNumber";

            return Cache::forget($cacheKey);
        }

        // Clear all traffic violation caches
        $vehicles = Vehicle::query()->whereNotNull('plate_number')->pluck('plate_number');
        $cleared = 0;

        foreach ($vehicles as $vehicle) {
            $cacheKey = "traffic_violations_$vehicle";
            if (Cache::forget($cacheKey)) {
                $cleared++;
            }
        }

        Log::info('Traffic violation caches cleared', ['cleared_count' => $cleared]);

        return true;
    }

    /**
     * Get cached violations
     */
    public function getCachedViolations(string $plateNumber): ?array
    {
        $cacheKey = "traffic_violations_$plateNumber";

        return Cache::get($cacheKey);
    }

    /**
     * Check if violations are cached
     */
    public function isCached(string $plateNumber): bool
    {
        $cacheKey = "traffic_violations_$plateNumber";

        return Cache::has($cacheKey);
    }

    /**
     * Get cache expiry time
     */
    public function getCacheExpiry(string $plateNumber): ?Carbon
    {
        $cacheKey = "traffic_violations_$plateNumber";

        if (! Cache::has($cacheKey)) {
            return null;
        }

        // This is a simplified approach - in production you might want to store expiry separately
        $data = Cache::get($cacheKey);
        if (isset($data['checked_at'])) {
            return Carbon::parse($data['checked_at'])->addHours(24);
        }

        return null;
    }

    /**
     * Check violations via real SMS to JPJ (alternative approach)
     */
    private function checkViaRealSms(string $plateNumber): ?array
    {
        try {
            // For direct SMS to JPJ, we would need a different approach
            // Since Twilio can't send to short codes, we could:
            // 1. Use a different SMS provider that supports short codes
            // 2. Use JPJ's web API if available
            // 3. Use a third-party service that interfaces with JPJ

            // For now, let's implement a webhook-based approach
            // where we send SMS via external service and receive response via webhook

            return $this->checkViaWebhookResponse($plateNumber);

        } catch (Exception $e) {
            Log::error('Real SMS check failed', [
                'plate_number' => $plateNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check for webhook response data
     */
    private function checkViaWebhookResponse(string $plateNumber): ?array
    {
        // Get all cache keys that match the pattern
        // Note: This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar

        for ($i = 0; $i < 10; $i++) {
            $possibleKey = "jpj_response_{$plateNumber}_".substr(md5($plateNumber.$i), 0, 8);
            if (Cache::has($possibleKey)) {
                $data = Cache::get($possibleKey);
                Log::info('Found JPJ webhook response', [
                    'plate_number' => $plateNumber,
                    'cache_key' => $possibleKey,
                ]);

                return $data;
            }
        }

        // If no webhook response found, try to trigger SMS check
        // This would be where you integrate with external SMS service
        // that can send to JPJ short codes

        return null;
    }

    /**
     * Get JPJ response by message SID (called from webhook)
     */
    public function getResponseByMessageSid(string $messageSid): ?array
    {
        $plateNumber = Cache::get("jpj_lookup_{$messageSid}");
        if (! $plateNumber) {
            return null;
        }

        $cacheKey = "jpj_response_{$plateNumber}_{$messageSid}";

        return Cache::get($cacheKey);
    }

    /**
     * Process real JPJ response from webhook
     */
    public function processWebhookResponse(string $plateNumber, array $responseData): void
    {
        // Update vehicle with real data
        $vehicle = Vehicle::where('plate_number', $plateNumber)->first();
        if ($vehicle) {
            $this->updateVehicleViolations($vehicle, $responseData);

            // Also update main cache
            $cacheKey = "traffic_violations_{$plateNumber}";
            Cache::put($cacheKey, $responseData, now()->addHours(24));

            Log::info('Vehicle updated with webhook JPJ response', [
                'vehicle_id' => $vehicle->id,
                'plate_number' => $plateNumber,
                'violations_count' => count($responseData['violations']),
            ]);
        }
    }
}
