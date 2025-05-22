<?php

namespace App\Infrastructure\Event\Listeners\Listeners;

use App\Infrastructure\Event\DonationMadeEvent;
use App\Infrastructure\Mail\DonationConfirmationMail;
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

        /** @var \App\Infrastructure\Persistence\Models\User $user */
        $user = $donation->user;

        // Send confirmation email
        Mail::to($user->email)->send(new DonationConfirmationMail($donation));
    }
}
