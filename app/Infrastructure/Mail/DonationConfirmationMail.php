<?php

namespace App\Infrastructure\Mail;


use Illuminate\Mail\Mailable;

class DonationConfirmationMail extends Mailable
{

    public $donation;

    /**
     * Create a new message instance.
     *
     * @param $campaign
     */
    public function __construct($donation)
    {
        $this->donation = $donation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Donation Confirmation')
                    ->view('emails.donation-confirmation')
                    ->with([
                        'donation' => $this->donation,
                    ]);
    }
}
