<?php

namespace App\Application\Campaign\Command;

class UpdateCampaignCommand
{
    public function __construct
    (public readonly int $campaignId,
    public readonly string $userId, // ID of the admin rejecting the campaign
    public object $dto // Add the $dto property
    )
    {
    }
}
