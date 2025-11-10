<?php

namespace App\Console\Commands;

use App\Services\BookingConflictResolutionService;
use Illuminate\Console\Command;

class CleanupExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-expired
                            {--dry-run : Show what would be cleaned up without making changes}
                            {--timeout=60 : Timeout in minutes for pending payments (default: 60)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired booking sessions and abandoned payments';

    public function __construct(
        private readonly BookingConflictResolutionService $bookingConflictResolutionService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of expired bookings...');

        $isDryRun = $this->option('dry-run');
        $timeoutMinutes = (int) $this->option('timeout');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Show current expired bookings
        $expiredBookings = \App\Models\Booking::query()
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subMinutes($timeoutMinutes))
            ->with(['vehicle', 'renter'])
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('No expired bookings found.');

            return 0;
        }

        $this->info("Found {$expiredBookings->count()} expired booking(s):");

        $table = [];
        foreach ($expiredBookings as $expiredBooking) {
            $table[] = [
                'ID' => $expiredBooking->id,
                'Vehicle' => "{$expiredBooking->vehicle->make} {$expiredBooking->vehicle->model}",
                'Renter' => $expiredBooking->renter->name,
                'Created' => $expiredBooking->created_at->diffForHumans(),
                'Amount' => 'RM '.number_format($expiredBooking->total_amount, 2),
                'Status' => $expiredBooking->status,
            ];
        }

        $this->table([
            'ID', 'Vehicle', 'Renter', 'Created', 'Amount', 'Status',
        ], $table);

        if ($isDryRun) {
            $this->info('DRY RUN: These bookings would be cancelled.');

            return 0;
        }

        if (! $this->confirm('Do you want to cancel these expired bookings?')) {
            $this->info('Operation cancelled.');

            return 0;
        }

        // Perform cleanup
        $cleanedCount = $this->bookingConflictResolutionService->cleanupExpiredBookings();

        $this->info("Successfully cancelled {$cleanedCount} expired booking(s).");

        // Show summary
        if ($cleanedCount > 0) {
            $this->info('Summary:');
            $this->line("• {$cleanedCount} bookings cancelled");
            $this->line('• Vehicle inventory freed up for new bookings');
            $this->line('• Users can now book these vehicles for the freed time slots');
        }

        return 0;
    }
}
