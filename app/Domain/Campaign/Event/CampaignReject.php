<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Event;

use App\Domain\Shared\Event\DomainEvent;
use DateTimeImmutable;

final class CampaignCancelled implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly int $campaignId,
        public readonly int $cancelledBy, // User or Admin who cancelled
        public readonly ?string $reason
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
