<?php

namespace App\Infrastructure\Event;

use App\Infrastructure\Persistence\Models\Campaign;

class CampaignApprovedEvent
{
    public function __construct(
        public Campaign $campaign,
        public string $approvedBy,
        public \DateTimeImmutable $approvedAt
    ) {
    }
}
