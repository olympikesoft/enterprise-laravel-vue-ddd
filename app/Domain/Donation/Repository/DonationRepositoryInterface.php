<?php

declare(strict_types=1);

namespace App\Domain\Donation\Repository;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Donation\Aggregate\Donation;
use App\Domain\Donation\ValueObject\DonationId;

interface DonationRepositoryInterface
{
    public function findById(DonationId $id): ?Donation;
    public function save(Donation $donation): void;
    public function nextIdentity(): DonationId;

    /**
     * @return Donation[]
     */
    // public function findByCampaignId(CampaignId $campaignId): array;
}