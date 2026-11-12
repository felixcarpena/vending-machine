<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

use Shared\Domain\DomainEvent;

interface EventBus
{
    public function publish(DomainEvent ...$events): void;
}
