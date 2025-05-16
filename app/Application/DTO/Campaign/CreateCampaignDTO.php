<?php

namespace App\Application\DTO\Campaign;

use Carbon\Carbon;

class CreateCampaignDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly float $goalAmount,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly int $userId
    ) {}
}