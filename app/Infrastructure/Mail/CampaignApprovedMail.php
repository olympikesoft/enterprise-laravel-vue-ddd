use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Queueable, SerializesModels;

<?php

namespace App\Infrastructure\Mail;

use Illuminate\Mail\Mailable;

class CampaignApprovedMail extends Mailable
{

    public $campaign;

    /**
     * Create a new message instance.
     *
     * @param $campaign
     */
    public function __construct($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Campaign Has Been Approved')
                    ->view('emails.campaign-approved')
                    ->with([
                        'campaignName' => $this->campaign->name,
                        'campaignOwner' => $this->campaign->owner->name,
                    ]);
    }
}
