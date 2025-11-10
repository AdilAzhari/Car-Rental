<?php

namespace App\Console\Commands;

use App\Services\FilamentCacheService;
use Illuminate\Console\Command;

class ClearFilamentCache extends Command
{
    protected $signature = 'filament:cache:clear
                            {--widgets : Clear only widget cache}
                            {--resources : Clear only resource cache}
                            {--navigation : Clear only navigation cache}';

    protected $description = 'Clear Filament caches for better performance';

    public function handle(): int
    {
        $this->info('Clearing Filament caches...');

        if ($this->option('widgets')) {
            FilamentCacheService::clearWidgetCache();
            $this->info('✓ Widget cache cleared');
        } elseif ($this->option('resources')) {
            FilamentCacheService::clearResourceCache();
            $this->info('✓ Resource cache cleared');
        } elseif ($this->option('navigation')) {
            FilamentCacheService::clearNavigationCache();
            $this->info('✓ Navigation cache cleared');
        } else {
            FilamentCacheService::clearAllCache();
            $this->info('✓ All Filament caches cleared');
        }

        return self::SUCCESS;
    }
}
