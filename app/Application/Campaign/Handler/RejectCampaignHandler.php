<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\RejectCampaignCommand;
use App\Infrastructure\Persistence\Models\Campaign; // Eloquent Model
use App\Infrastructure\Persistence\Models\User; // Eloquent Model for User
use App\Application\Services\NotificationServiceInterface; // Optional
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Exception; // General exception
use Carbon\Carbon;

class RejectCampaignHandler
{
    public function __construct(
        private ?NotificationServiceInterface $notificationService = null // Optional
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws AuthorizationException
     * @throws Exception
     */
    public function handle(RejectCampaignCommand $command): Campaign
    {
        $campaign = Campaign::findOrFail($command->campaignId);
        $rejectingUser = User::findOrFail($command->userId);

        // Authorization: Only admin can reject campaigns
        if (!$rejectingUser->is_admin) {
            throw new AuthorizationException('You are not authorized to reject campaigns.');
        }

        if ($campaign->status !== Campaign::STATUS_PENDING) {
            throw new Exception("Only pending campaigns can be rejected. Current status: {$campaign->status}");
        }

        $campaign->status = Campaign::STATUS_REJECTED;

        $campaign->save();

        if ($this->notificationService) {
             $this->notificationService->sendCampaignRejectedNotification($campaign, $rejectingUser);
         }

        return $campaign;
    }
}
