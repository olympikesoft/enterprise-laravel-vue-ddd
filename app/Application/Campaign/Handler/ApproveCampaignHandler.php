<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Application\Services\NotificationServiceInterface; // Optional
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApproveCampaignHandler
{
   /* public function __construct(
        private ?NotificationServiceInterface $notificationService = null // Optional
    ) {}*/

    /**
     * @throws ModelNotFoundException
     * @throws \Exception // For other errors like already approved
     */
    public function handle(ApproveCampaignCommand $command): Campaign
    {
        $campaign = Campaign::findOrFail($command->campaignId);

        if ($campaign->status === Campaign::STATUS_APPROVED) {
            throw new \Exception("Campaign is already approved.");
        }

        if ($campaign->status !== Campaign::STATUS_PENDING) {
            throw new \Exception("Campaign cannot be approved from its current status: {$campaign->status}");
        }

        $campaign->status = Campaign::STATUS_APPROVED;
        $campaign->save();

        //if ($this->notificationService) {
           //  $this->notificationService->sendCampaignApprovedNotification($campaign, \user:);
        //}

        return $campaign;
    }
}
