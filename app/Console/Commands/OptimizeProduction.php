<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:production {--force : Force optimization even in non-production environment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application for production by caching configuration, routes, views, and events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if we're in production environment
        if (!app()->environment('production') && !$this->option('force')) {
            $this->error('This command should only be run in production environment!');
            $this->info('Use --force flag to run in other environments.');
            return self::FAILURE;
        }

        $this->info('Starting production optimization...');
        $this->newLine();

        // Clear all caches first
        $this->task('Clearing existing caches', function () {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('event:clear');
        });

        // Cache configuration
        $this->task('Caching configuration', function () {
            Artisan::call('config:cache');
        });

        // Cache routes
        $this->task('Caching routes', function () {
            Artisan::call('route:cache');
        });

        // Cache views
        $this->task('Caching views', function () {
            Artisan::call('view:cache');
        });

        // Cache events
        $this->task('Caching events', function () {
            Artisan::call('event:cache');
        });

        // Run optimize command
        $this->task('Running Laravel optimize', function () {
            Artisan::call('optimize');
        });

        // Create storage link if it doesn't exist
        $this->task('Creating storage link', function () {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        });

        // Restart queue workers
        $this->task('Restarting queue workers', function () {
            Artisan::call('queue:restart');
        });

        $this->newLine();
        $this->info('✓ Production optimization completed successfully!');
        $this->newLine();

        // Show recommendations
        $this->info('Recommendations:');
        $this->line('• Monitor application logs: tail -f storage/logs/laravel.log');
        $this->line('• Check queue workers: php artisan queue:monitor');
        $this->line('• Verify Redis connection: redis-cli ping');
        $this->line('• Monitor supervisor status: sudo supervisorctl status');

        $this->newLine();
        $this->warn('Security Checklist:');
        $this->line('□ APP_DEBUG is set to false');
        $this->line('□ APP_KEY is generated and secure');
        $this->line('□ Database credentials are secure');
        $this->line('□ SSL certificate is installed');
        $this->line('□ File permissions are correct (775 for storage)');
        $this->line('□ Backups are configured');

        return self::SUCCESS;
    }
}
