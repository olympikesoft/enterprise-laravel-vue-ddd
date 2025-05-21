<?php
// No additional code needed here for the $PLACEHOLDER$
namespace App\Providers;

use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use App\Domain\Donation\Repository\DonationRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDonationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       $this->app->bind(CampaignRepositoryInterface::class, EloquentCampaignRepository::class
        );
        $this->app->bind(DonationRepositoryInterface::class, EloquentDonationRepository::class
        );
        /*$this->app->bind(
            \App\Application\Services\PaymentServiceInterface::class,
            \App\Infrastructure\Payment\DummyPaymentService::class
        );*/
        /*$this->app->bind(
            \App\Application\Services\NotificationServiceInterface::class,
            \App\Infrastructure\Notification\EmailNotificationService::class
        );*/
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

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
