<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Models\User;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;

interface NotificationServiceInterface
{
    public function sendCampaignCreatedConfirmation(Campaign $campaign, User $creator): void;
    public function sendCampaignNeedsApprovalNotification(Campaign $campaign, array $adminEmails): void;
    public function sendCampaignApprovedNotification(Campaign $campaign, User $user): void;
    public function sendCampaignRejectedNotification(Campaign $campaign, string $reason): void;
    public function sendCampaignGoalReachedNotification(Campaign $campaign): void;
    public function sendNewDonationToCampaignOwnerNotification(Donation $donation, Campaign $campaign): void;
}
