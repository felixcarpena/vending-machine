<?php

declare(strict_types=1);

namespace Vending\Application\InsertCoin;

use Shared\Application\Bus\Command;
use Vending\Domain\Coin;
use Vending\Domain\VendingMachineId;

final readonly class InsertCoin implements Command
{
    public function __construct(
        public VendingMachineId $machineId,
        public Coin $coin,
    ) {}
}
