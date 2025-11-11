<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use App\Models\Vehicle;
use App\Observers\BookingObserver;
use App\Observers\PaymentObserver;
use App\Observers\ReviewObserver;
use App\Observers\UserObserver;
use App\Observers\VehicleObserver;
use App\Policies\BookingPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiclePolicy;
use App\Repositories\VehicleRepository;
use App\Services\TransactionService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(VehicleRepository::class);

        // Register services
        $this->app->singleton(TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        // Register policies
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);

        // Register model observers
        Vehicle::observe(VehicleObserver::class);
        Booking::observe(BookingObserver::class);
        Payment::observe(PaymentObserver::class);
        User::observe(UserObserver::class);
        Review::observe(ReviewObserver::class);

        ResetPassword::createUrlUsing(fn (object $notifiable, string $token): string => config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}");


        FilamentColor::register([
            'danger' => [
        50 => 'oklch(0.969 0.015 12.422)',
        100 => 'oklch(0.941 0.03 12.58)',
        200 => 'oklch(0.892 0.058 10.001)',
        300 => 'oklch(0.81 0.117 11.638)',
        400 => 'oklch(0.712 0.194 13.428)',
        500 => 'oklch(0.645 0.246 16.439)',
        600 => 'oklch(0.586 0.253 17.585)',
        700 => 'oklch(0.514 0.222 16.935)',
        800 => 'oklch(0.455 0.188 13.697)',
        900 => 'oklch(0.41 0.159 10.272)',
        950 => 'oklch(0.271 0.105 12.094)',
    ],
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);
    }
}
