<?php

namespace App\Commands\Spotlight;

use LivewireUI\Spotlight\Spotlight;
use LivewireUI\Spotlight\SpotlightCommand;

class ViewBookingsCommand extends SpotlightCommand
{
    protected string $name = 'View Bookings';

    protected string $description = 'View all bookings in the system';

    protected array $synonyms = ['bookings', 'reservations'];

    public function execute(Spotlight $spotlight): void
    {
        $spotlight->redirect('/admin/bookings');
    }
}
