<?php

declare(strict_types=1);

namespace Shared\Domain;

abstract class AggregateRoot
{
    /** @var list<DomainEvent> */
    private array $events = [];

    /** @return list<DomainEvent> */
    final public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    final protected function record(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
