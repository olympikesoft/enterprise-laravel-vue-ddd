<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\DeleteCampaignCommand;
use App\Domain\Campaign\Repository\CampaignRepositoryInterface;
use App\Domain\Donation\Repository\DonationRepositoryInterface;
use App\Domain\Campaign\ValueObject\CampaignStatus;
use Exception;

class DeleteCampaignHandler
{
    public function __construct(
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly DonationRepositoryInterface $donationRepository // Optional, for checks
    ) {}

    public function handle(DeleteCampaignCommand $command): void
    {
        $campaign = $this->campaignRepository->findById($command->campaignId);

        if (!$campaign) {
            throw new Exception("Campaign with ID {$command->campaignId} not found.");
        }

        if ($campaign->getCreatorId() !== $command->actingUserId) {
            throw new Exception("User is not authorized to delete this campaign.");
        }

        if ($this->donationRepository->hasCompletedDonationsForCampaign($command->campaignId)) {
            throw new \LogicException("Cannot delete campaign with completed donations. Consider archiving instead.");
        }

        if (!in_array($campaign->getStatus()->getValue(), [CampaignStatus::PENDING])) {
             throw new \LogicException("Campaign cannot be deleted in its current status: " . $campaign->getStatus()->getValue());
        }

        $this->campaignRepository->delete($campaign);

        // event(new CampaignDeleted($campaign->getId()));
    }
}
