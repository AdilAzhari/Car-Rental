<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizePerformance extends Command
{
    protected $signature = 'app:optimize-performance';

    protected $description = 'Run all performance optimization commands';

    public function handle(): int
    {
        $this->info('🚀 Optimizing application performance...');

        // Clear all caches first
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('filament:cache:clear');

        $this->info('✅ Caches cleared');

        // Cache configurations
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('filament:optimize');

        $this->info('✅ Configurations cached');

        // Run Laravel optimizations
        $this->call('optimize');

        $this->info('✅ Laravel optimizations applied');

        // Warm up Filament caches
        $this->call('filament:cache:warm');

        $this->info('✅ Filament caches warmed up');

        // Build frontend assets if in development
        if (app()->environment('local', 'development')) {
            $this->info('🔨 Building frontend assets...');
            $exitCode = 0;
            $this->runInBackground('npm run build', $exitCode);

            if ($exitCode === 0) {
                $this->info('✅ Frontend assets built successfully');
            } else {
                $this->warn('⚠️  Frontend build had some issues, but continuing...');
            }
        }

        $this->info('🎉 Performance optimization completed!');
        $this->info('📊 Recommended next steps:');
        $this->line('   • Monitor application performance');
        $this->line('   • Run this command after deployments');
        $this->line('   • Consider enabling OPcache in production');

        return self::SUCCESS;
    }

    private function runInBackground(string $command, int &$exitCode): void
    {
        $process = popen($command, 'r');
        if ($process) {
            while (! feof($process)) {
                $line = fgets($process);
                if ($line) {
                    $this->line('   '.trim($line));
                }
            }
            $exitCode = pclose($process);
        }
    }
}
