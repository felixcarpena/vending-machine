<?php

declare(strict_types=1);

namespace Vending\Application\StartMachine;

use Shared\Application\Bus\Command;
use Vending\Domain\VendingMachineId;

final readonly class StartMachine implements Command
{
    public function __construct(public VendingMachineId $machineId) {}
}
