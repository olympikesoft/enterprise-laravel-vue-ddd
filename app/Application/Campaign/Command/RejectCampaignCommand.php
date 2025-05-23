<?php

declare(strict_types=1);

namespace App\Application\Campaign\Command;

class RejectCampaignCommand
{
    public function __construct(
        public readonly string $campaignId,
        public readonly string $userId, // ID of the admin rejecting the campaign
        public readonly ?string $reason
    ) {}
}
