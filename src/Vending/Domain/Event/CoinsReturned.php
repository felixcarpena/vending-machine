<?php

declare(strict_types=1);

namespace Vending\Domain\Event;

use Shared\Domain\DomainEvent;
use Vending\Domain\Coins;
use Vending\Domain\VendingMachineId;

final readonly class CoinsReturned implements DomainEvent
{
    public function __construct(
        public VendingMachineId $machineId,
        public Coins $coins,
    ) {}
}
