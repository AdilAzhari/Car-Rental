<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FilamentLocalizationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add RTL support for Arabic
        View::composer('filament::*', function ($view): void {
            $currentLocale = app()->getLocale();
            $isRtl = in_array($currentLocale, ['ar', 'he', 'fa', 'ur']);

            $view->with('isRtl', $isRtl);
            $view->with('currentLocale', $currentLocale);
        });

        // Configure Filament for RTL languages
        Filament::serving(function (): void {
            $currentLocale = app()->getLocale();

            if (in_array($currentLocale, ['ar', 'he', 'fa', 'ur'])) {
                // Add RTL CSS classes
                View::share('filamentRtl', true);
            }
        });
    }
}
