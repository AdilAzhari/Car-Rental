<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\InsuranceExpiringSoon;
use Illuminate\Console\Command;

class SendProactiveNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-proactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send proactive notifications for insurance expiry and other time-sensitive events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for proactive notifications...');

        $this->checkInsuranceExpiry();

        $this->info('Proactive notifications sent successfully!');

        return Command::SUCCESS;
    }

    /**
     * Check for vehicles with insurance expiring soon
     */
    protected function checkInsuranceExpiry(): void
    {
        // Get vehicles with insurance expiring in 30, 14, 7, 3, or 1 days
        $thresholds = [30, 14, 7, 3, 1];

        foreach ($thresholds as $threshold) {
            $expiryDate = now()->addDays($threshold)->toDateString();

            $vehicles = Vehicle::whereDate('insurance_expiry', $expiryDate)
                ->with('owner')
                ->get();

            foreach ($vehicles as $vehicle) {
                // Notify vehicle owner
                if ($vehicle->owner) {
                    $vehicle->owner->notify(new InsuranceExpiringSoon($vehicle, $threshold));
                    $this->info("Notified owner about {$vehicle->plate_number} insurance expiring in {$threshold} days");
                }

                // Notify admins
                $admins = User::where('role', UserRole::ADMIN)->get();
                foreach ($admins as $admin) {
                    $admin->notify(new InsuranceExpiringSoon($vehicle, $threshold));
                }
            }
        }
    }
}
