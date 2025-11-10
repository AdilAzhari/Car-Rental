<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\JpjSmsService;
use App\Services\TrafficViolationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestJpjIntegration extends Command
{
    protected $signature = 'jpj:test {--plate= : Test specific plate number} {--webhook : Test webhook parsing} {--clear-cache : Clear violation cache}';

    protected $description = 'Test JPJ traffic violation integration';

    public function handle(): int
    {
        $this->info('🚗 Testing JPJ Traffic Violation Integration');
        $this->newLine();

        if ($this->option('clear-cache')) {
            $this->clearCache();

            return 0;
        }

        if ($this->option('webhook')) {
            $this->testWebhookParsing();

            return 0;
        }

        $plateNumber = $this->option('plate') ?: 'ABC1234';
        $this->testCompleteFlow($plateNumber);

        return 0;
    }

    private function testCompleteFlow(string $plateNumber): void
    {
        $this->info("Testing complete JPJ integration flow for plate: {$plateNumber}");
        $this->newLine();

        // Test 1: JPJ SMS Service
        $this->info('1. Testing JPJ SMS Service...');
        $jpjSmsService = app(JpjSmsService::class);
        $smsResult = $jpjSmsService->checkTrafficViolations($plateNumber);

        if ($smsResult['success']) {
            $this->info("✅ SMS sent successfully (Message ID: {$smsResult['message_id']})");
        } else {
            $this->warn('⚠️  SMS service unavailable, using test mode');
        }

        // Test 2: Traffic Violation Service
        $this->info('2. Testing Traffic Violation Service...');
        $trafficViolationService = app(TrafficViolationService::class);

        // Find or create test vehicle
        $vehicle = Vehicle::where('plate_number', $plateNumber)->first();
        if (! $vehicle) {
            $this->warn("No vehicle found with plate {$plateNumber}, creating test vehicle...");
            $vehicle = Vehicle::factory()->create(['plate_number' => $plateNumber]);
            $this->info("✅ Test vehicle created with ID: {$vehicle->id}");
        }

        // Check violations
        $violationData = $trafficViolationService->checkVehicleViolations($vehicle);

        $this->info('3. Violation Check Results:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Plate Number', $violationData['plate_number'] ?? 'N/A'],
                ['Has Violations', $violationData['has_violations'] ? 'Yes' : 'No'],
                ['Pending Violations', $violationData['has_pending_violations'] ? 'Yes' : 'No'],
                ['Total Violations', count($violationData['violations'])],
                ['Total Fines', 'RM '.number_format($violationData['total_fines_amount'], 2)],
                ['Checked At', $violationData['checked_at'] ?? 'N/A'],
            ]
        );

        // Show violation details
        if (! empty($violationData['violations'])) {
            $this->newLine();
            $this->info('Violation Details:');
            foreach ($violationData['violations'] as $i => $violation) {
                $this->line('  '.($i + 1).". {$violation['type']} - RM{$violation['fine_amount']} ({$violation['status']})");
                $this->line("     Location: {$violation['location']}");
                $this->line("     Date: {$violation['date']}");
                if (isset($violation['description'])) {
                    $this->line("     Description: {$violation['description']}");
                }
                $this->newLine();
            }
        }

        // Test 3: Cache functionality
        $this->info('4. Testing Cache Functionality...');
        $cacheKey = "traffic_violations_{$plateNumber}";
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            $this->info('✅ Data cached successfully');
            $this->line('   Cache expiry: '.$trafficViolationService->getCacheExpiry($plateNumber)?->format('Y-m-d H:i:s'));
        } else {
            $this->warn('⚠️  Data not found in cache');
        }

        // Test 4: Vehicle update
        $this->info('5. Testing Vehicle Update...');
        $vehicle->refresh();
        $this->table(
            ['Field', 'Value'],
            [
                ['Total Violations Count', $vehicle->total_violations_count],
                ['Total Fines Amount', 'RM '.number_format($vehicle->total_fines_amount, 2)],
                ['Has Pending Violations', $vehicle->has_pending_violations ? 'Yes' : 'No'],
                ['Last Checked', $vehicle->violations_last_checked?->format('Y-m-d H:i:s')],
            ]
        );

        $this->newLine();
        $this->info('🎉 JPJ Integration test completed!');
    }

    private function testWebhookParsing(): void
    {
        $this->info('Testing JPJ Webhook Response Parsing');
        $this->newLine();

        $jpjSmsService = app(JpjSmsService::class);

        // Test scenarios
        $testCases = [
            [
                'name' => 'No Violations',
                'message' => "JPJ SAMAN ABC1234\nTIADA SAMAN TERTUNGGAK\nTARIKH: ".now()->format('d/m/Y'),
                'expected_violations' => 0,
            ],
            [
                'name' => 'Single Violation',
                'message' => "JPJ SAMAN DEF5678\nKESALAHAN LAJU KM 234.5\nRM 150.00\nTARIKH: ".now()->subDays(10)->format('d/m/Y'),
                'expected_violations' => 1,
            ],
            [
                'name' => 'Multiple Violations',
                'message' => "JPJ SAMAN GHI9012\nKESALAHAN LAJU RM 150.00\nLAMPU MERAH RM 300.00\nPARKING RM 80.00\nJUMLAH: RM 530.00",
                'expected_violations' => 3,
            ],
        ];

        foreach ($testCases as $testCase) {
            $this->info("Testing: {$testCase['name']}");
            $this->line("Message: {$testCase['message']}");

            $result = $jpjSmsService->processJpjResponse($testCase['message'], 'TEST_'.uniqid());

            if ($result) {
                $actualViolations = count($result['violations']);
                if ($actualViolations === $testCase['expected_violations']) {
                    $this->info("✅ Parsed correctly: {$actualViolations} violations found");
                } else {
                    $this->error("❌ Parse error: Expected {$testCase['expected_violations']}, got {$actualViolations}");
                }

                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Plate Number', $result['plate_number']],
                        ['Has Violations', $result['has_violations'] ? 'Yes' : 'No'],
                        ['Total Fines', 'RM '.number_format($result['total_fines_amount'], 2)],
                    ]
                );
            } else {
                $this->error('❌ Failed to parse message');
            }

            $this->newLine();
        }
    }

    private function clearCache(): void
    {
        $this->info('Clearing JPJ violation cache...');

        $trafficViolationService = app(TrafficViolationService::class);
        $cleared = $trafficViolationService->clearCache();

        if ($cleared) {
            $this->info('✅ Cache cleared successfully');
        } else {
            $this->warn('⚠️  No cache to clear or operation failed');
        }

        // Also clear JPJ-specific caches
        $vehicles = Vehicle::whereNotNull('plate_number')->pluck('plate_number');
        $jpjCacheCleared = 0;

        foreach ($vehicles as $vehicle) {
            $patterns = [
                "jpj_response_{$vehicle}_*",
                "jpj_request_{$vehicle}_*",
                'jpj_lookup_*',
            ];

            foreach ($patterns as $pattern) {
                // This is a simplified cache clear - in production you might use Redis SCAN
                for ($i = 0; $i < 100; $i++) {
                    $key = str_replace('*', substr(md5($vehicle.$i), 0, 8), $pattern);
                    if (Cache::forget($key)) {
                        $jpjCacheCleared++;
                    }
                }
            }
        }

        $this->info("Cleared {$jpjCacheCleared} JPJ-specific cache entries");
    }
}
