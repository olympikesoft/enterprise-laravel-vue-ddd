<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Application\Services\NotificationServiceInterface; // Optional
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CanceledCampaignHandler
{
    public function __construct(
        private ?NotificationServiceInterface $notificationService = null // Optional
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws \Exception // For other errors like already approved
     */
    public function handle(ApproveCampaignCommand $command): Campaign
    {
        $campaign = Campaign::findOrFail($command->campaignId);

        if ($campaign->status === Campaign::STATUS_COMPLETED) {
            return $campaign; // Idempotent
        }


        $campaign->status = Campaign::STATUS_CANCELLED;
        $campaign->save();

        if ($this->notificationService) {
            // $this->notificationService->sendCampaignApprovedNotification($campaign);
        }

        return $campaign;
    }
}
