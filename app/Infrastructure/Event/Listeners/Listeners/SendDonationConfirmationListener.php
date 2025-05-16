<?php

namespace App\Listeners;

use App\Domain\Donation\Event\DonationSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDonationConfirmationListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DonationSucceeded $event): void
    {
        //
    }
}
