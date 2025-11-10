<?php

namespace App\Console\Commands;

use App\Services\FilamentCacheService;
use Illuminate\Console\Command;

class WarmFilamentCache extends Command
{
    protected $signature = 'filament:cache:warm';

    protected $description = 'Warm up Filament caches for better performance';

    public function handle(): int
    {
        $this->info('Warming up Filament caches...');

        FilamentCacheService::warmUpCache();

        $this->info('✓ Filament caches warmed up successfully');

        return self::SUCCESS;
    }
}
