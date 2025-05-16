<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Event;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Employee\ValueObject\EmployeeId; // ID of admin who approved
use App\Domain\Shared\Event\DomainEvent;
use DateTimeImmutable;

final class CampaignApproved implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly CampaignId $campaignId,
        public readonly EmployeeId $approvedBy // ID of the admin/employee who approved
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