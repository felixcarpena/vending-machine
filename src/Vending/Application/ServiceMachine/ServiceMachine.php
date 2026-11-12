<?php

declare(strict_types=1);

namespace Vending\Application\ServiceMachine;

use Shared\Application\Bus\Command;
use Vending\Domain\Coins;
use Vending\Domain\StockLevels;
use Vending\Domain\VendingMachineId;

final readonly class ServiceMachine implements Command
{
    public function __construct(
        public VendingMachineId $machineId,
        public StockLevels $stock,
        public Coins $change,
    ) {}
}
