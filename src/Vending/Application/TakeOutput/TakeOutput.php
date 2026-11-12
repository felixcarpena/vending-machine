<?php

declare(strict_types=1);

namespace Vending\Application\TakeOutput;

use Shared\Application\Bus\Command;
use Vending\Domain\VendingMachineId;

final readonly class TakeOutput implements Command
{
    public function __construct(public VendingMachineId $machineId) {}
}
