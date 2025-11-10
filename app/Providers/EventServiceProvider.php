<?php

namespace App\Providers;

use App\Listeners\SendWelcomeNotification;
use App\Listeners\UpdateLastLoginTimestamp;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendWelcomeNotification::class,
        ],
        Login::class => [
            SendWelcomeNotification::class,
            UpdateLastLoginTimestamp::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    #[\Override]
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    #[\Override]
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
