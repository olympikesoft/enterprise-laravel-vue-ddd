use App\Infrastructure\Event\Events\DonationMadeEvent;
use Illuminate\Support\Facades\Mail;

<?php

namespace App\Infrastructure\Event\Listeners\Listeners;

use App\Infrastructure\Event\DonationMadeEvent;
use App\Mail\DonationConfirmationMail;
use Illuminate\Support\Facades\Mail;

class SendDonationConfirmationListener
{
    /**
     * Handle the event.
     *
     * @param  DonationMadeEvent  $event
     * @return void
     */
    public function handle(DonationMadeEvent $event)
    {
        $donation = $event->donation;

        // Send confirmation email
        Mail::to($donation->donor_email)->send(new DonationConfirmationMail($donation));
    }
}
