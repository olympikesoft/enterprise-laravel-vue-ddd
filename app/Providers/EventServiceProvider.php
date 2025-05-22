<?php

namespace App\Providers;

use App\Infrastructure\Event\DonationMadeEvent;
use App\Infrastructure\Event\Listeners\SendDonationConfirmationListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
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
            SendEmailVerificationNotification::class,
        ],

        DonationMadeEvent::class => [
            SendDonationConfirmationListener::class,
            // You can add more listeners here for the same event
            // e.g., NotifyCampaignOwnerListener::class,
            // UpdateCampaignStatisticsListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(
            DonationMadeEvent::class,
            SendDonationConfirmationListener::class
        );
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
