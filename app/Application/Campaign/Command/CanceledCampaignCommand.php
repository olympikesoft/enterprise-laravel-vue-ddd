<?php

declare(strict_types=1);

namespace App\Application\Campaign\Command;

class CanceledCampaignCommand
{
    public function __construct(
        public readonly string $campaignId,
        public readonly string $adminUserId,
        public readonly ?string $reason
    ) {}
}
