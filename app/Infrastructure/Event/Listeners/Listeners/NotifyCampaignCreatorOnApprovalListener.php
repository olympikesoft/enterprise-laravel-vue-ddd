<?php

namespace App\Listeners;

use App\Domain\Campaign\Event\CampaignApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCampaignCreatorOnApprovalListener
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
    public function handle(CampaignApproved $event): void
    {
        //
    }
}
