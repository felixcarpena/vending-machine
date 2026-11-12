<?php

declare(strict_types=1);

namespace Vending\Application\ReturnMoney;

use Shared\Application\Bus\Command;
use Vending\Domain\VendingMachineId;

final readonly class ReturnMoney implements Command
{
    public function __construct(public VendingMachineId $machineId) {}
}
