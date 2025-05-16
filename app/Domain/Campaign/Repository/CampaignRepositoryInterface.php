<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Repository;

use App\Domain\Campaign\Aggregate\Campaign;
use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Employee\ValueObject\EmployeeId; // For finding by creator

interface CampaignRepositoryInterface
{
    public function findById(CampaignId $id): ?Campaign;
    public function save(Campaign $campaign): void;
    public function nextIdentity(): CampaignId; // Useful for generating IDs before saving

    /**
     * @return Campaign[]
     */
    // public function findByCreator(EmployeeId $creatorId): array;

    /**
     * @return Campaign[]
     */
    // public function findActiveCampaigns(): array;
}