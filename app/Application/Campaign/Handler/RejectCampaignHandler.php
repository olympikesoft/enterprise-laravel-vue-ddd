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

        // Campaign can only be rejected if currently pending
        if ($campaign->status !== Campaign::STATUS_PENDING) {
            throw new Exception("Only pending campaigns can be rejected. Current status: {$campaign->status}");
        }

        // Update campaign status and rejection details
        $campaign->status = Campaign::STATUS_REJECTED;
        $campaign->rejection_reason = $command->reason; // Store the reason for rejection
        $campaign->rejected_at = Carbon::now();
        $campaign->rejected_by_id = $rejectingUser->id;

        // If a campaign was previously approved, clear those fields
        $campaign->approved_at = null;
        $campaign->approved_by_id = null;

        $campaign->save();

        // Optional: Send notification about rejection
        // if ($this->notificationService) {
        //     $this->notificationService->sendCampaignRejectedNotification($campaign, $rejectingUser);
        // }

        return $campaign;
    }
}
