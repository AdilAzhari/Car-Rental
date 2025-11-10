<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\TrafficViolationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckTrafficViolations extends Command
{
    protected $signature = 'violations:check
                            {--vehicle=* : Specific vehicle IDs to check}
                            {--plate=* : Specific plate numbers to check}
                            {--force : Force check even if cached}
                            {--dry-run : Show what would be checked without actually checking}
                            {--clear-cache : Clear violation cache before checking}';

    protected $description = 'Check traffic violations for vehicles using SMS service';

    public function handle(TrafficViolationService $trafficViolationService): int
    {
        $this->info('🚗 Starting traffic violation checks...');

        if ($this->option('clear-cache')) {
            $this->info('Clearing violation cache...');
            $trafficViolationService->clearCache();
        }

        $vehicles = $this->getVehiclesToCheck();

        if ($vehicles->isEmpty()) {
            $this->warn('No vehicles found to check.');

            return self::SUCCESS;
        }

        $this->info("Found {$vehicles->count()} vehicle(s) to check.");

        if ($this->option('dry-run')) {
            $this->showDryRun($vehicles, $trafficViolationService);

            return self::SUCCESS;
        }

        $results = $this->checkVehicles($vehicles, $trafficViolationService);
        $this->displayResults($results);

        return self::SUCCESS;
    }

    private function getVehiclesToCheck()
    {
        $query = Vehicle::whereNotNull('plate_number');

        // Filter by specific vehicle IDs
        if ($this->option('vehicle')) {
            $vehicleIds = $this->option('vehicle');
            $query->whereIn('id', $vehicleIds);
        }

        // Filter by specific plate numbers
        if ($this->option('plate')) {
            $plateNumbers = $this->option('plate');
            $query->whereIn('plate_number', $plateNumbers);
        }

        // If no specific filters and not forcing, only check vehicles needing refresh
        if (! $this->option('vehicle') && ! $this->option('plate') && ! $this->option('force')) {
            $query->where(function ($q): void {
                $q->whereNull('violations_last_checked')
                    ->orWhere('violations_last_checked', '<', now()->subHours(24));
            });
        }

        return $query->get();
    }

    private function showDryRun($vehicles, TrafficViolationService $trafficViolationService): void
    {
        $this->info('DRY RUN - No actual SMS will be sent');
        $this->newLine();

        foreach ($vehicles as $vehicle) {
            $cached = $trafficViolationService->isCached($vehicle->plate_number);
            $lastChecked = $vehicle->violations_last_checked
                ? $vehicle->violations_last_checked->format('Y-m-d H:i:s')
                : 'Never';

            $status = $cached ? '📋 CACHED' : '📡 WILL CHECK';

            $this->line("Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->plate_number})");
            $this->line("  Status: {$status}");
            $this->line("  Last checked: {$lastChecked}");

            if ($cached) {
                $expiry = $trafficViolationService->getCacheExpiry($vehicle->plate_number);
                $this->line("  Cache expires: {$expiry->format('Y-m-d H:i:s')}");
            }

            $this->newLine();
        }
    }

    private function checkVehicles($vehicles, TrafficViolationService $trafficViolationService): array
    {
        $results = [
            'total_checked' => 0,
            'violations_found' => 0,
            'errors' => 0,
            'cached_results' => 0,
            'new_checks' => 0,
            'total_fines' => 0,
            'pending_violations' => 0,
        ];

        $progressBar = $this->output->createProgressBar($vehicles->count());
        $progressBar->start();

        foreach ($vehicles as $vehicle) {
            try {
                $results['total_checked']++;

                $wasCached = $trafficViolationService->isCached($vehicle->plate_number);

                $this->line(''); // New line for better formatting
                $this->info("Checking: {$vehicle->plate_number} ({$vehicle->make} {$vehicle->model})");

                $violationData = $trafficViolationService->checkVehicleViolations($vehicle);

                if ($wasCached) {
                    $results['cached_results']++;
                    $this->line('  📋 Retrieved from cache');
                } else {
                    $results['new_checks']++;
                    $this->line('  📡 Checked via SMS');
                }

                if ($violationData['has_violations']) {
                    $results['violations_found']++;
                    $violationCount = count($violationData['violations']);
                    $this->warn("  ⚠️  Found {$violationCount} violation(s)");

                    if ($violationData['has_pending_violations']) {
                        $results['pending_violations']++;
                        $this->error('  🚨 Has pending violations!');
                    }

                    $results['total_fines'] += $violationData['total_fines_amount'];
                } else {
                    $this->line('  ✅ No violations found');
                }

                // Update vehicle record
                $trafficViolationService->updateVehicleViolations($vehicle, $violationData);

                // Rate limiting for non-cached requests
                if (! $wasCached) {
                    sleep(2);
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $this->error('  ❌ Error: '.$e->getMessage());

                Log::error('Traffic violation check failed in command', [
                    'vehicle_id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    private function displayResults(array $results): void
    {
        $this->info('📊 Traffic Violation Check Results');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Vehicles Checked', $results['total_checked']],
                ['New SMS Checks', $results['new_checks']],
                ['Cached Results', $results['cached_results']],
                ['Vehicles with Violations', $results['violations_found']],
                ['Vehicles with Pending Violations', $results['pending_violations']],
                ['Total Outstanding Fines', 'RM '.number_format($results['total_fines'], 2)],
                ['Errors', $results['errors']],
            ]
        );

        if ($results['pending_violations'] > 0) {
            $this->warn("⚠️  {$results['pending_violations']} vehicle(s) have pending traffic violations requiring attention!");
        }

        if ($results['errors'] > 0) {
            $this->error("❌ {$results['errors']} error(s) occurred during checking. Check logs for details.");
        }

        $this->info('✅ Traffic violation check completed!');
    }
}
