<?php

namespace App\Providers;

use App\Services\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(SmsService::class, function ($app): ?\App\Services\SmsService {
            try {
                return new SmsService;
            } catch (\Exception $e) {
                // Log the error but don't fail the application bootstrap
                \Illuminate\Support\Facades\Log::warning('SmsService not available: '.$e->getMessage());

                return null;
            }
        });
    }

    public function boot(): void
    {
        //
    }
}
