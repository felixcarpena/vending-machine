<?php

declare(strict_types=1);

namespace Tests\Double;

use Shared\Application\Bus\EventBus;
use Shared\Domain\DomainEvent;

final class RecordingEventBus implements EventBus
{
    /** @var list<DomainEvent> */
    public private(set) array $published = [];

    public function publish(DomainEvent ...$events): void
    {
        $this->published = [...$this->published, ...array_values($events)];
    }
}
