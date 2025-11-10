<?php

namespace App\Commands\Spotlight;

use LivewireUI\Spotlight\Spotlight;
use LivewireUI\Spotlight\SpotlightCommand;

class DashboardCommand extends SpotlightCommand
{
    protected string $name = 'Dashboard';

    protected string $description = 'Go to the main dashboard';

    protected array $synonyms = ['home', 'main'];

    public function execute(Spotlight $spotlight): void
    {
        $spotlight->redirect('/admin');
    }
}
