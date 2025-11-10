<?php

namespace App\Console\Commands;

use App\Services\FilamentQueryOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestFilamentPerformance extends Command
{
    protected $signature = 'filament:test-performance';

    protected $description = 'Test Filament query optimization performance';

    public function handle(): int
    {
        $this->info('🔬 Testing Filament Query Optimization Performance...');

        $filamentQueryOptimizationService = app(FilamentQueryOptimizationService::class);

        // Test 1: Dashboard Stats Performance
        $this->info('📊 Testing Dashboard Stats...');
        $startTime = microtime(true);
        $stats = $filamentQueryOptimizationService->getDashboardStats();
        $dashboardTime = microtime(true) - $startTime;

        $this->table(['Metric', 'Value'], [
            ['Total Users', $stats['total_users']],
            ['Total Vehicles', $stats['total_vehicles']],
            ['Active Bookings', $stats['active_bookings']],
            ['Today Revenue', $stats['today_revenue']],
            ['Average Rating', $stats['avg_rating']],
            ['Execution Time', round($dashboardTime * 1000, 2).'ms'],
        ]);

        // Test 2: Optimized vs Non-optimized Queries
        $this->info('⚡ Comparing Query Performance...');

        // Test Booking Query Performance
        $startTime = microtime(true);
        $optimizedBookings = $filamentQueryOptimizationService->getOptimizedBookingQuery()->limit(10)->get();
        $optimizedTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $regularBookings = \App\Models\Booking::with(['renter', 'vehicle', 'vehicle.owner'])->limit(10)->get();
        $regularTime = microtime(true) - $startTime;

        $this->table(['Query Type', 'Records', 'Time (ms)', 'Improvement'], [
            ['Optimized Booking Query', $optimizedBookings->count(), round($optimizedTime * 1000, 2).'ms', '-'],
            ['Regular Booking Query', $regularBookings->count(), round($regularTime * 1000, 2).'ms', round((($regularTime - $optimizedTime) / $regularTime) * 100, 1).'%'],
        ]);

        // Test 3: User Query Performance
        $startTime = microtime(true);
        $optimizedUsers = $filamentQueryOptimizationService->getOptimizedUserQuery()->limit(10)->get();
        $optimizedUserTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $regularUsers = \App\Models\User::withCount(['vehicles', 'bookings', 'reviews'])->limit(10)->get();
        $regularUserTime = microtime(true) - $startTime;

        $this->table(['Query Type', 'Records', 'Time (ms)', 'Improvement'], [
            ['Optimized User Query', $optimizedUsers->count(), round($optimizedUserTime * 1000, 2).'ms', '-'],
            ['Regular User Query', $regularUsers->count(), round($regularUserTime * 1000, 2).'ms', round((($regularUserTime - $optimizedUserTime) / $regularUserTime) * 100, 1).'%'],
        ]);

        // Test 4: Vehicle Query Performance
        $startTime = microtime(true);
        $optimizedVehicles = $filamentQueryOptimizationService->getOptimizedVehicleQuery()->limit(10)->get();
        $optimizedVehicleTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $regularVehicles = \App\Models\Vehicle::with(['owner', 'images'])->withCount(['bookings', 'reviews'])->limit(10)->get();
        $regularVehicleTime = microtime(true) - $startTime;

        $this->table(['Query Type', 'Records', 'Time (ms)', 'Improvement'], [
            ['Optimized Vehicle Query', $optimizedVehicles->count(), round($optimizedVehicleTime * 1000, 2).'ms', '-'],
            ['Regular Vehicle Query', $regularVehicles->count(), round($regularVehicleTime * 1000, 2).'ms', round((($regularVehicleTime - $optimizedVehicleTime) / $regularVehicleTime) * 100, 1).'%'],
        ]);

        // Test 5: Database Query Count
        $this->info('📈 Testing Query Count Reduction...');

        DB::enableQueryLog();

        // Optimized query
        $filamentQueryOptimizationService->getOptimizedBookingQuery()->limit(5)->get();
        $optimizedQueries = count(DB::getQueryLog());

        DB::flushQueryLog();

        // Regular query with N+1 problem
        \App\Models\Booking::limit(5)->get()->each(function ($booking): void {
            $booking->renter->name;
            $booking->vehicle->make;
            $booking->vehicle->owner->name;
        });
        $regularQueries = count(DB::getQueryLog());

        DB::disableQueryLog();

        $this->table(['Approach', 'Queries Executed', 'Reduction'], [
            ['Optimized (Eager Loading)', $optimizedQueries, '-'],
            ['Regular (N+1 Problem)', $regularQueries, round((($regularQueries - $optimizedQueries) / $regularQueries) * 100, 1).'%'],
        ]);

        // Test 6: Bulk Operations Performance
        $this->info('🔄 Testing Bulk Operations...');

        if (\App\Models\Booking::count() > 0) {
            $sampleIds = \App\Models\Booking::limit(5)->pluck('id')->toArray();

            $startTime = microtime(true);
            $filamentQueryOptimizationService->getBulkOperationQuery('Booking', $sampleIds)->get();
            $bulkTime = microtime(true) - $startTime;

            $startTime = microtime(true);
            \App\Models\Booking::whereIn('id', $sampleIds)->get();
            $regularBulkTime = microtime(true) - $startTime;

            $this->table(['Operation Type', 'Records', 'Time (ms)', 'Improvement'], [
                ['Optimized Bulk Query', count($sampleIds), round($bulkTime * 1000, 2).'ms', '-'],
                ['Regular Bulk Query', count($sampleIds), round($regularBulkTime * 1000, 2).'ms', round((($regularBulkTime - $bulkTime) / $regularBulkTime) * 100, 1).'%'],
            ]);
        } else {
            $this->warn('No booking records found for bulk operation testing.');
        }

        // Summary
        $this->info('✅ Performance testing completed!');
        $this->info('💡 Key Benefits:');
        $this->line('   • Reduced database queries through eager loading');
        $this->line('   • Optimized pagination with consistent ordering');
        $this->line('   • Performance monitoring for slow queries');
        $this->line('   • Efficient bulk operations');
        $this->line('   • Cached dashboard statistics');

        if ($dashboardTime > 0.1) {
            $this->warn('⚠️  Dashboard stats query is slow (>'.round($dashboardTime * 1000).'ms). Consider adding database indexes.');
        }

        return self::SUCCESS;
    }
}
