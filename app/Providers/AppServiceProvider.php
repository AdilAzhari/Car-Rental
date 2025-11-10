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
    }
}
