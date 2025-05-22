<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Event;

use App\Domain\Shared\Event\DomainEvent;
use DateTimeImmutable;

final class CampaignApproved implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly int $campaignId,
        public readonly int $approvedBy // ID of the admin/employee who approved
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getAggregateId(): int
    {
        return $this->campaignId;
    }
}
