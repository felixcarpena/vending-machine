<?php

declare(strict_types=1);

namespace Vending\Application\BuyProduct;

use Shared\Application\Bus\Command;
use Vending\Domain\Selector;
use Vending\Domain\VendingMachineId;

final readonly class BuyProduct implements Command
{
    public function __construct(
        public VendingMachineId $machineId,
        public Selector $selector,
    ) {}
}
