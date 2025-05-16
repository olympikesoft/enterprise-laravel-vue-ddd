<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

trait RaisesDomainEvents
{
    private array $domainEvents = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}