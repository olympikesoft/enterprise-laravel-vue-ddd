<?php
// app/Providers/PaymentServiceProvider.php

namespace App\Providers;

use App\Application\Services\PaymentServiceInterface;
use App\Infrastructure\Payment\MockPaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Always use mock payment service for now
        $this->app->bind(PaymentServiceInterface::class, MockPaymentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
