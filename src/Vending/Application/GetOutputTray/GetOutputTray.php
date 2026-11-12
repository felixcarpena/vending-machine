<?php

declare(strict_types=1);

namespace Vending\Application\GetOutputTray;

use Shared\Application\Bus\Query;
use Vending\Domain\VendingMachineId;

/** @implements Query<OutputTrayContents> */
final readonly class GetOutputTray implements Query
{
    public function __construct(public VendingMachineId $machineId) {}
}
