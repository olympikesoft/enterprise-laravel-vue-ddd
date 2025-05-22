<?php

namespace App\Application\DTO\Campaign;

use Carbon\Carbon;

class UpdateCampaignDTO
{
    public function __construct(
        public readonly int $campaignId,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?float $goalAmount = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?string $status = null,
        public readonly ?int $actingUserId = null,
    ) {}
}
