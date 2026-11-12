<?php

declare(strict_types=1);

namespace Vending\Domain\Event;

use Shared\Domain\DomainEvent;
use Vending\Domain\Coin;
use Vending\Domain\VendingMachineId;

final readonly class CoinInserted implements DomainEvent
{
    public function __construct(
        public VendingMachineId $machineId,
        public Coin $coin,
    ) {}
}
