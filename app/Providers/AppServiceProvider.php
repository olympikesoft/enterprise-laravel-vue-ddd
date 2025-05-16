<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       /* $this->app->bind(
            \App\Domain\Campaign\Repository\CampaignRepositoryInterface::class,
            \App\Infrastructure\Persistence\Eloquent\EloquentCampaignRepository::class
        );
        $this->app->bind(
            \App\Domain\Donation\Repository\DonationRepositoryInterface::class,
            \App\Infrastructure\Persistence\Eloquent\EloquentDonationRepository::class
        );
        $this->app->bind(
            \App\Domain\Employee\Repository\EmployeeRepositoryInterface::class,
            \App\Infrastructure\Persistence\Eloquent\EloquentEmployeeRepository::class
        );
        $this->app->bind(
            \App\Application\Services\PaymentServiceInterface::class,
            \App\Infrastructure\Payment\DummyPaymentService::class // Or StripePaymentService later
        );
        $this->app->bind(
            \App\Application\Services\NotificationServiceInterface::class,
            \App\Infrastructure\Notification\EmailNotificationService::class
        );*/
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
