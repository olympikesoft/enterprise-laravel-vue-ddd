<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class DonationConfirmationMail extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param mixed $donation
     */
    public function __construct(
        public readonly mixed $donation
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Donation Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.donation-confirmation',
            with: [
                'donation' => $this->donation,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
