<?php

declare(strict_types=1);

namespace App\Domain\Donation\Event;

use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;

final class DonationSucceeded implements DomainEvent
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly int $donationId,
        public readonly int $campaignId,
        public readonly Money $amount,
        public readonly string $transactionReference // From payment gateway
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getAggregateId(): int
    {
        return $this->donationId;
    }
}
