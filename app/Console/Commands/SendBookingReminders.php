<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminderSms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Send SMS reminders for bookings starting tomorrow';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Get bookings starting tomorrow
        $bookings = Booking::with(['renter', 'vehicle'])
            ->where('status', 'confirmed')
            ->where('start_date', '>=', now()->addDay()->startOfDay())
            ->where('start_date', '<=', now()->addDay()->endOfDay())
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings starting tomorrow found.');

            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} booking(s) starting tomorrow.");

        $sent = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            if (! $booking->renter->phone) {
                $this->warn("Booking #{$booking->id}: Renter {$booking->renter->name} has no phone number.");
                $failed++;

                continue;
            }

            if ($dryRun) {
                $this->line("Would send reminder to {$booking->renter->name} ({$booking->renter->phone}) for booking #{$booking->id}");
                $sent++;
            } else {
                try {
                    $booking->renter->notify(new BookingReminderSms($booking));
                    $this->info("Sent reminder to {$booking->renter->name} for booking #{$booking->id}");
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for booking #{$booking->id}: {$e->getMessage()}");
                    Log::error('Booking reminder failed', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                }
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->info("  Reminders sent: {$sent}");
        if ($failed > 0) {
            $this->warn("  Failed: {$failed}");
        }

        if ($dryRun) {
            $this->warn('This was a dry run. Use without --dry-run to actually send reminders.');
        }

        return self::SUCCESS;
    }
}
