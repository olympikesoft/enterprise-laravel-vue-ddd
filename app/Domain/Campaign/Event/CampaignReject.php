<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Event;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Shared\Event\DomainEvent;
use DateTimeImmutable;

final class CampaignCancelled implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly CampaignId $campaignId,
        public readonly EmployeeId $cancelledBy, // User or Admin who cancelled
        public readonly ?string $reason
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getAggregateId(): string
    {
        return $this->campaignId->toString();
    }
}
