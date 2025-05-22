<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Event;

use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;

final class CampaignCreated implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly int $campaignId,
        public readonly int $creatorId,
        public readonly string $title,
        public readonly Money $goalAmount,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate
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
