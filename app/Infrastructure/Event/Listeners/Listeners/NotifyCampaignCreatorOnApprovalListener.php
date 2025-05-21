use App\Events\CampaignApprovedEvent;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

<?php

namespace App\Infrastructure\Event\Listeners;

use App\Infrastructure\Event\CampaignApprovedEvent;
use App\Infrastructure\Mail\CampaignApprovedMail;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Mail;

class NotifyCampaignCreatorOnApprovalListener
{
    /**
     * Handle the event.
     *
     * @param  CampaignApprovedEvent  $event
     * @return void
     */
    public function handle(CampaignApprovedEvent $event)
    {
        $campaign = $event->campaign;
        $creator = $campaign->creatorId;

        if ($creator instanceof User) {
            // Send notification email to the campaign creator
            Mail::to($creator->email)->send(new CampaignApprovedMail($campaign));
        }
    }
}
