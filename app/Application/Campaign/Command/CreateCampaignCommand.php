<?php

namespace App\Application\Campaign\Command;

use App\Application\DTO\Campaign\CreateCampaignDTO;

class CreateCampaignCommand
{
    public function __construct(public readonly CreateCampaignDTO $dto)
    {
    }
}