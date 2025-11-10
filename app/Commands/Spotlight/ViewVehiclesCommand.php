<?php

namespace App\Commands\Spotlight;

use LivewireUI\Spotlight\Spotlight;
use LivewireUI\Spotlight\SpotlightCommand;

class ViewVehiclesCommand extends SpotlightCommand
{
    protected string $name = 'View Vehicles';

    protected string $description = 'View all vehicles in the system';

    protected array $synonyms = ['vehicles', 'cars'];

    public function execute(Spotlight $spotlight): void
    {
        $spotlight->redirect('/admin/vehicles');
    }
}
