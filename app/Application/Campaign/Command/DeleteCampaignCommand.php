<?php

namespace App\Application\Campaign\Command;

class DeleteCampaignCommand
{
    public function __construct(
        public readonly int $campaignId,
        public readonly int $actingUserId
    ) {}
}
