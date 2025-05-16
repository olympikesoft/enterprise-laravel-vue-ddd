<?php

namespace App\Application\Campaign\Command;

class ApproveCampaignCommand
{
    public function __construct(public readonly int $campaignId)
    {
    }
}