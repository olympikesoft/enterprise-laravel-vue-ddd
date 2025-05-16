<?php

declare(strict_types=1);

namespace App\Domain\Donation\Event;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Donation\ValueObject\DonationId;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;

final class DonationInitiated implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly DonationId $donationId,
        public readonly CampaignId $campaignId,
        public readonly Money $amount,
        public readonly ?EmployeeId $donorId, // Nullable for anonymous donations
        public readonly ?string $donorName // For anonymous donations
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getAggregateId(): string
    {
        return $this->donationId->toString();
    }
}