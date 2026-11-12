<?php

declare(strict_types=1);

namespace Vending\Domain;

use Shared\Domain\AggregateRoot;
use Vending\Domain\Event\MachineServiced;

final class VendingMachine extends AggregateRoot
{
    public function __construct(
        public readonly VendingMachineId $id,
        public private(set) AvailableChange $availableChange,
        public private(set) Slots $slots,
    ) {}

    public function service(StockLevels $stock, Coins $change): void
    {
        $this->slots = $this->slots->restockedTo($stock);
        $this->availableChange = AvailableChange::of($change);
        $this->record(new MachineServiced($this->id));
    }
}
